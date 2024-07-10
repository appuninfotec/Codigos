<?php
require_once "topo.php"; // Inclui o cabeçalho comum

// Verifica se houve sucesso ao editar o produto
$mensagem_sucesso = "";
if (isset($_GET["ok"])) {
    $mensagem_sucesso = "Produto atualizado com sucesso.";
}

// Verifica se houve erro ao processar a solicitação
$mensagem_erro = "";
if (isset($_GET["erro"])) {
    $mensagem_erro = "Ocorreu um erro ao processar a solicitação.";
}

if (isset($_GET['id'])) {
    $produto_id = $_GET['id'];

    // Seleciona o produto a ser editado
    $query = $connect->prepare("SELECT * FROM produtos WHERE id = :id");
    $query->execute([':id' => $produto_id]);
    $produto = $query->fetch(PDO::FETCH_OBJ);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $produto_id = $_POST['produto_id'];
    $nome = $_POST['cad_nome'];
    $ingredientes = $_POST['cad_ingrediente'];
    $categoria = $_POST['cad_cat'];
    $valor = $_POST['cad_valor'];
    $link = $_POST['cad_link'];
    $visivel = $_POST['visivel'];
    $status = $_POST['status'];
    $imagem_atual = $_POST['imagem_atual'];

    // Verifica se uma nova imagem foi enviada
    if (!empty($_FILES['pic']['name'])) {
        $imagem = $_FILES['pic']['name'];
        $target_dir = "../img/fotos_produtos/";
        $target_file = $target_dir . basename($_FILES["pic"]["name"]);
        move_uploaded_file($_FILES["pic"]["tmp_name"], $target_file);
    } else {
        // Mantém a imagem atual se nenhuma nova imagem for enviada
        $imagem = $imagem_atual;
    }

    try {
        $sql = "UPDATE produtos SET nome = :nome, ingrediente = :ingrediente, categoria = :categoria, valor = :valor, link = :link, visivel = :visivel, status = :status, foto = :foto WHERE id = :id";
        $stmt = $connect->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':ingrediente' => $ingredientes,
            ':categoria' => $categoria,
            ':valor' => $valor,
            ':link' => $link,
            ':visivel' => $visivel,
            ':status' => $status,
            ':foto' => $imagem,
            ':id' => $produto_id
        ]);

        header("Location: editarproduto.php?id=$produto_id&ok");
    } catch (PDOException $e) {
        header("Location: editarproduto.php?id=$produto_id&erro");
    }
}
?>

<div class="slim-mainpanel">
    <div class="container">
        <?php if (!empty($mensagem_erro)) { ?>
            <div class="alert alert-warning" role="alert">
                <i class="fa fa-asterisk" aria-hidden="true"></i> <?php echo $mensagem_erro; ?>
            </div>
        <?php } ?>
        <?php if (!empty($mensagem_sucesso)) { ?>
            <div class="alert alert-success" role="alert">
                <i class="fa fa-thumbs-o-up" aria-hidden="true"></i> <?php echo $mensagem_sucesso; ?>
            </div>
        <?php } ?>

        <div class="section-wrapper mg-b-20">
            <label class="section-title"><i class="fa fa-check-square-o" aria-hidden="true"></i> EDITAR PRODUTO</label>
            <hr>
            <form action="editarproduto.php?id=<?php echo $produto_id; ?>" method="post" enctype="multipart/form-data">
                <input type="hidden" name="update" value="ok">
                <input type="hidden" name="produto_id" value="<?php echo $produto->id; ?>">
                <input type="hidden" name="imagem_atual" value="<?php echo $produto->foto; ?>">
                <div class="form-layout">
                    <div class="row mg-b-25">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Nome: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" name="cad_nome" value="<?php echo $produto->nome; ?>" required>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label class="form-control-label">Ingredientes:</label>
                                <input type="text" class="form-control" name="cad_ingrediente" value="<?php echo $produto->ingrediente; ?>">
                                <p>Atenção: Deixe N se não tiver descrição.</p>
                            </div>
                        </div><!-- col-8 -->
                    </div><!-- row -->

                    <div class="row mg-b-25">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label">Categoria: <span class="tx-danger">*</span></label>
                                <select class="form-control" name="cad_cat" required>
                                    <option value="" disabled><b>Selecione...</b></option>
                                    <?php 
                                    $selcat = $connect->query("SELECT * FROM categorias ORDER BY posicao ASC");
                                    while ($dadossel = $selcat->fetch(PDO::FETCH_OBJ)) { 
                                    ?>
                                    <option value="<?php echo $dadossel->id; ?>" <?php echo ($produto->categoria == $dadossel->id) ? 'selected' : ''; ?>><?php echo $dadossel->nome; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div><!-- col-3 -->
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Valor: <span class="tx-danger">*</span></label>
                                <input type="text" class="dinheiro form-control" name="cad_valor" maxlength="10" value="<?php echo $produto->valor; ?>" required>
                            </div>
                        </div><!-- col-2 -->
                        <div class="col-lg-7">
                            <div class="form-group">
                                <label class="form-control-label">Sua imagem será diminuída para 600x400px. <span class="tx-danger">*</span></label>
                                <input type="file" name="pic" class="form-control">
                                <p>Imagem atual: <img src="../img/fotos_produtos/<?php echo $produto->foto; ?>" width="100"></p>
                            </div>
                        </div><!-- col-7 -->
                    </div><!-- row -->

                    <div class="row mg-b-25">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label">Link do Botão:</label>
                                <input type="url" class="form-control" name="cad_link" value="<?php echo $produto->link; ?>">
                            </div>
                        </div><!-- col-12 -->
                    </div><!-- row -->

                    <div class="row mg-b-25">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">Visível:</label>
                                <select class="form-control" name="visivel">
                                    <option value="G" <?php echo ($produto->visivel == "G") ? 'selected' : ''; ?>>Todos os dias</option>
                                    <option value="1" <?php echo ($produto->visivel == "1") ? 'selected' : ''; ?>>Segunda</option>
                                    <option value="2" <?php echo ($produto->visivel == "2") ? 'selected' : ''; ?>>Terça</option>
                                    <option value="3" <?php echo ($produto->visivel == "3") ? 'selected' : ''; ?>>Quarta</option>
                                    <option value="4" <?php echo ($produto->visivel == "4") ? 'selected' : ''; ?>>Quinta</option>
                                    <option value="5" <?php echo ($produto->visivel == "5") ? 'selected' : ''; ?>>Sexta</option>
                                    <option value="6" <?php echo ($produto->visivel == "6") ? 'selected' : ''; ?>>Sábado</option>
                                    <option value="0" <?php echo ($produto->visivel == "0") ? 'selected' : ''; ?>>Domingo</option>
                                </select>
                            </div>
                        </div><!-- col-6 -->
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label class="form-control-label">Status:</label>
                                <select class="form-control" name="status">
                                    <option value="1" <?php echo ($produto->status == "1") ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="0" <?php echo ($produto->status== "0") ? 'selected' : ''; ?>>Bloqueado</option>
                                </select>
                            </div>
                        </div><!-- col-6 -->
                    </div><!-- row -->

                    <div class="form-layout-footer" align="center">
                        <button class="btn btn-primary bd-0" type="submit" name="update">Salvar <i class="fa fa-arrow-right"></i></button>
                    </div><!-- form-layout-footer -->
                </div><!-- form-layout -->
            </form>
        </div><!-- section-wrapper -->
    </div><!-- container -->
</div><!-- slim-mainpanel -->

<script src="../lib/jquery/js/jquery.js"></script>
<script src="../lib/datatables/js/jquery.dataTables.js"></script>
<script src="../lib/datatables-responsive/js/dataTables.responsive.js"></script>
<script src="../lib/select2/js/select2.min.js"></script>

<script>
    $(function(){
        'use strict';

        $('#datatable1').DataTable({
            responsive: true,
            language: {
                searchPlaceholder: 'Buscar...',
                sSearch: '',
                lengthMenu: '_MENU_ ítens',
            }
        });

        $('#datatable2').DataTable({
            bLengthChange: false,
            searching: false,
            responsive: true
        });

        // Select2
        $('.dataTables_length select').select2({ minimumResultsForSearch: Infinity });

        // Máscara para campo de dinheiro
        $('.dinheiro').mask('#.##0,00', {reverse: true});
    });
</script>
<script src="../js/slim.js"></script>
</body>
</html>