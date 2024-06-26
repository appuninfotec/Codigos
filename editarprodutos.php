<?php
require_once "topo.php"; // Inclui o cabeçalho comum

// Verifica se foi submetido o formulário de edição
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editarproduto'])) {
    // Recebe e sanitiza os dados do formulário
    $codigoc = $_POST['codigopro'];
    $nome = $_POST['cad_nome'];
    $ingrediente = $_POST['cad_ingrediente'];
    $categoria = $_POST['cad_cat'];
    $valor = $_POST['cad_valor'];
    $foto = $_FILES['pic']['name']; // Nome do arquivo de imagem
    $link = $_POST['cad_link'];

    // Upload da imagem, se houver uma nova
    if (!empty($foto)) {
        $target_dir = "../img/fotos_produtos/";
        $target_file = $target_dir . basename($_FILES["pic"]["name"]);
        move_uploaded_file($_FILES["pic"]["tmp_name"], $target_file);
    }

    try {
        // Atualiza o produto na tabela produtos
        $sql = "UPDATE produtos SET nome = :nome, ingredientes = :ingrediente, categoria = :categoria, valor = :valor";
        if (!empty($foto)) {
            $sql .= ", foto = :foto";
        }
        $sql .= " WHERE id = :id AND idu = :idu";

        $stmt = $connect->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':ingrediente', $ingrediente);
        $stmt->bindParam(':categoria', $categoria);
        $stmt->bindParam(':valor', $valor);
        if (!empty($foto)) {
            $stmt->bindParam(':foto', $foto);
        }
        $stmt->bindParam(':id', $codigoc);
        $stmt->bindParam(':idu', $cod_id);
        $stmt->execute();

        // Verifica se há um link associado na tabela produto_links
        $sqlLink = "SELECT * FROM produto_links WHERE produto_id = :produto_id";
        $stmtLink = $connect->prepare($sqlLink);
        $stmtLink->bindParam(':produto_id', $codigoc);
        $stmtLink->execute();

        if ($stmtLink->rowCount() > 0) {
            // Atualiza o link existente
            $sqlUpdateLink = "UPDATE produto_links SET link = :link WHERE produto_id = :produto_id";
            $stmtUpdateLink = $connect->prepare($sqlUpdateLink);
            $stmtUpdateLink->bindParam(':link', $link);
            $stmtUpdateLink->bindParam(':produto_id', $codigoc);
            $stmtUpdateLink->execute();
        } else {
            // Insere um novo link
            $sqlInsertLink = "INSERT INTO produto_links (produto_id, link) VALUES (:produto_id, :link)";
            $stmtInsertLink = $connect->prepare($sqlInsertLink);
            $stmtInsertLink->bindParam(':produto_id', $codigoc);
            $stmtInsertLink->bindParam(':link', $link);
            $stmtInsertLink->execute();
        }

        // Redireciona para a página de produtos com mensagem de sucesso
        header("Location: produtos.php?ok");
        exit();
    } catch (PDOException $e) {
        // Em caso de erro, redireciona para a página de produtos com mensagem de erro
        header("Location: produtos.php?erro");
        exit();
    }
}

// Carrega os dados do produto a ser editado
$codigoc = $_POST['codigoc'];
$editarcat = $connect->query("SELECT * FROM produtos WHERE id='$codigoc' AND idu='$cod_id'");
$dadoscat = $editarcat->fetch(PDO::FETCH_OBJ);

// Busca o link associado ao produto, se existir
$linkQuery = $connect->prepare("SELECT link FROM produto_links WHERE produto_id = :produto_id");
$linkQuery->bindParam(':produto_id', $dadoscat->id);
$linkQuery->execute();
$linkData = $linkQuery->fetch(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Produtos</title>
    <!-- Inclua aqui seus estilos CSS -->
    <link rel="stylesheet" href="caminho_para_seu_css.css">
</head>
<body>
<div class="slim-mainpanel">
    <div class="container">
        <?php if (isset($_GET["erro"])) { ?>
            <div class="alert alert-warning" role="alert">
                <i class="fa fa-asterisk" aria-hidden="true"></i> Ocorreu um erro ao processar a solicitação.
            </div>
        <?php } ?>
        <?php if (isset($_GET["ok"])) { ?>
            <div class="alert alert-success" role="alert">
                <i class="fa fa-thumbs-o-up" aria-hidden="true"></i> Produto atualizado com sucesso.
            </div>
        <?php } ?>

        <div class="section-wrapper mg-b-20">
            <label class="section-title"><i class="fa fa-check-square-o" aria-hidden="true"></i> EDITAR PRODUTO</label>
            <hr>
            <form action="" method="post" enctype="multipart/form-data">
                <input type="hidden" name="editarproduto" value="ok">
                <input type="hidden" name="codigopro" value="<?php echo $codigoc; ?>">
                <div class="form-layout">
                    <div class="row mg-b-25">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Nome: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" name="cad_nome" value="<?php echo htmlspecialchars($dadoscat->nome); ?>" required>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label class="form-control-label">Ingredientes: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" name="cad_ingrediente" value="<?php echo htmlspecialchars($dadoscat->ingredientes); ?>" required>
                            </div>
                        </div><!-- col-4 -->
                    </div>
                    <div class="row mg-b-25">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label">Categoria: <span class="tx-danger">*</span></label>
                                <select class="form-control" name="cad_cat" required>
                                    <?php
                                    // Lista todas as categorias
                                    $selcatx = $connect->query("SELECT * FROM categorias WHERE id = '" . $dadoscat->categoria . "'");
                                    while ($dadosselx = $selcatx->fetch(PDO::FETCH_OBJ)) {
                                        echo '<option value="' . htmlspecialchars($dadosselx->id) . '">' . htmlspecialchars($dadosselx->nome) . '</option>';
                                    }
                                    $selcat = $connect->query("SELECT * FROM categorias WHERE idu = '$cod_id' ORDER BY posicao ASC");
                                    while ($dadossel = $selcat->fetch(PDO::FETCH_OBJ)) {
                                        echo '<option value="' . htmlspecialchars($dadossel->id) . '">- ' . htmlspecialchars($dadossel->nome) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Valor: <span class="tx-danger">*</span></label>
                                <input type="text" class="dinheiro form-control" id="dinheiro" name="cad_valor" value="<?php echo htmlspecialchars($dadoscat->valor); ?>" required>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-lg-7">
                            <div class="form-group">
                                <label class="form-control-label">Imagem: <span class="tx-danger">*</span></label>
                                <input type="file" name="pic" class="form-control">
                            </div>
                        </div><!-- col-4 -->
                        <div class="row mg-b-25">
                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label class="form-control-label">Link do Botão:</label>
                                    <input type="url" class="form-control" name="cad_link" value="<?php echo $linkData ? htmlspecialchars($linkData->link) : ''; ?>">
                                </div>
                            </div><!-- col-12 -->
                        </div><!-- row -->
                    </div><!-- row -->

                    <div class="form-layout-footer" align="center">
                        <button class="btn btn-primary bd-0">Salvar <i class="fa fa-arrow-right"></i></button>
                    </div><!-- form-layout-footer -->
                </div><!-- form-layout -->
            </form>
        </div><!-- section-wrapper -->
    </div><!-- container -->
</div><!-- slim-mainpanel -->

<!-- Inclua aqui seus scripts JavaScript -->
<script src="../lib/jquery/js/jquery.js"></script>
<script src="../lib/jquery.mask/jquery.mask.min.js"></script>
<script>
    $(document).ready(function() {
        $('.dinheiro').mask('#,##0.00', {reverse: true});
    });
</script>
</body>
</html>
