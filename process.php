<?php
// Função para buscar o HTML da página
function fetchHTML($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $html = curl_exec($ch);
    curl_close($ch);
    return $html;
}

// Função para extrair os metadados da página
function extractMetaData($html) {
    $doc = new DOMDocument();
    @$doc->loadHTML($html);
    $metaTags = $doc->getElementsByTagName('meta');
    $data = [
        'image' => '',
        'price' => ''
    ];

    foreach ($metaTags as $meta) {
        if ($meta->getAttribute('property') === 'og:image') {
            $data['image'] = $meta->getAttribute('content');
        }
        if ($meta->getAttribute('property') === 'og:price:amount' || $meta->getAttribute('name') === 'price') {
            $data['price'] = $meta->getAttribute('content');
        }
    }

    // Adicione outras extrações necessárias, por exemplo, de tags específicas
    return $data;
}

// Função para criar uma imagem com os metadados
function createImageWithMetaData($imageURL, $price) {
    $img = imagecreatefromjpeg($imageURL);
    $black = imagecolorallocate($img, 0, 0, 0);
    $font = __DIR__ . '/arial.ttf'; // Caminho para a fonte TTF

    imagettftext($img, 20, 0, 10, 30, $black, $font, "Price: $" . $price);

    // Salvar a imagem
    $outputPath = 'output.jpg';
    imagejpeg($img, $outputPath);
    imagedestroy($img);

    return $outputPath;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $productURL = $_POST["productUrl"];

    // Buscar e extrair os metadados
    $html = fetchHTML($productURL);
    $metaData = extractMetaData($html);

    if ($metaData['image'] && $metaData['price']) {
        // Criar a imagem com os metadados
        $outputImage = createImageWithMetaData($metaData['image'], $metaData['price']);
        echo "Imagem criada em: <a href='$outputImage'>$outputImage</a>";
    } else {
        echo "Não foi possível encontrar a imagem ou o preço.";
    }
}
?>