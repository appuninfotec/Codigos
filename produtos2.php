<?php
require_once "topo.php"; // Inclui o cabeçalho comum

// Conexão com o banco de dados
$connect = new PDO("mysql:host=localhost;dbname=unindeli_delivery", "unindeli_uninfotec", "senha");

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

// Verifica se o produto foi desativado com sucesso
$produto_desativado = "";
if (isset($_GET["desativado"])) {
    $produto_desativado = "Produto desativado com sucesso.";
}

// Verifica se o produto foi ativado com sucesso
$produto_ativado = "";
if (isset($_GET["ativado"])) {
    $produto_ativado = "Produto ativado com sucesso.";
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
        <?php if (!empty($produto_desativado)) { ?>
            <div class="alert alert-info" role="alert">
                <i class="fa fa-eye-slash" aria-hidden="true"></i> <?php echo $produto_desativado; ?>
            </div>
        <?php } ?>
        <?php if (!empty($produto_ativado)) { ?>
            <div class="alert alert-info" role="alert">
                <i class="fa fa-eye" aria-hidden="true"></i> <?php echo $produto_ativado; ?>
            </div>
        <?php } ?>

        <div class="section-wrapper mg-b-20">
            <label class="section-title"><i class="fa fa-check-square-o" aria-hidden="true"></i> CADASTRO DE PRODUTOS</label>
            <hr>
            <form action="processa_produto.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cadpro" value="ok">
                <div class="form-layout">
                    <div class="row mg-b-25">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <label class="form-control-label">Nome: <span class="tx-danger">*</span></label>
                                <input type="text" class="form-control" name="cad_nome" required>
                            </div>
                        </div><!-- col-4 -->
                        <div class="col-lg-8">
                            <div class="form-group">
                                <label class="form-control-label">Ingredientes:</label>
                                <input type="text" class="form-control" name="cad_ingrediente" value="N">
                                <p>Atenção: Deixe N se não tiver descrição.</p>
                            </div>
                        </div><!-- col-8 -->
                    </div><!-- row -->

                    <div class="row mg-b-25">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label class="form-control-label">Categoria: <span class="tx-danger">*</span></label>
                                <select class="form-control" name="cad_cat" required>
                                    <option value="" disabled selected><b>Selecione...</b></option>
                                    <?php 
                                    $selcat = $connect->query("SELECT * FROM categorias WHERE idu = '$cod_id' ORDER BY posicao ASC");
                                    while ($dadossel = $selcat->fetch(PDO::FETCH_OBJ)) { 
                                    ?>
                                    <option value="<?php echo $dadossel->id; ?>"><?php echo $dadossel->nome; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div><!-- col-3 -->
                        <div class="col-lg-2">
                            <div class="form-group">
                                <label class="form-control-label">Valor: <span class="tx-danger">*</span></label>
                                <input type="text" class="dinheiro form-control" id="dinheiro" name="cad_valor" maxlength="10" value="0,00" required>
                            </div>
                        </div><!-- col-2 -->
                        <div class="col-lg-7">
                            <div class="form-group">
                                <label class="form-control-label">Sua imagem será diminuída para 600x400px. <span class="tx-danger">*</span></label>
                                <input type="file" name="pic" id="pic" class="form-control">
                            </div>
                        </div><!-- col-7 -->
                    </div><!-- row -->

                    <div class="row mg-b-25">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <label class="form-control-label">Link do Botão:</label>
                                <input type="url" class="form-control" name="cad_link" value="">
                            </div>
                        </div><!-- col-12 -->
                    </div><!-- row -->

                    <div class="form-layout-footer" align="center">
                        <button class="btn btn-primary bd-0">Salvar <i class="fa fa-arrow-right"></i></button>
                    </div><!-- form-layout-footer -->
                </div><!-- form-layout -->
            </form>
        </div><!-- section-wrapper -->

        <div class="section-wrapper">
            <label class="section-title"><i class="fa fa-check-square-o" aria-hidden="true"></i> Lista</label>
            <hr>
            <div class="table-wrapper">
                <table id="datatable1" class="table display responsive nowrap" width="100%">
                    <thead>
                        <tr>
                            <th>IMG</th>
                            <th>Categoria</th>
                            <th>Nome</th>
                            <th>Visível</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $dadpro = $connect->query("SELECT p.*, pl.link FROM produtos p LEFT JOIN produto_links pl ON p.id = pl.produto_id WHERE p.idu = '$cod_id' ORDER BY p.nome ASC");
                        while ($dadcate = $dadpro->fetch(PDO::FETCH_OBJ)) {
                            $buscat = $connect->query("SELECT * FROM categorias WHERE id =  '$dadcate->categoria'");
                            $buscat = $buscat->fetch(PDO::FETCH_OBJ);

                            if($dadcate->visivel=="G"){$visi = "Todos os dias";}
                            elseif($dadcate->visivel=="1"){$visi = "Segunda";}
                            elseif($dadcate->visivel=="2"){$visi = "Terça";}
                            elseif($dadcate->visivel=="3"){$visi = "Quarta";}
                            elseif($dadcate->visivel=="4"){$visi = "Quinta";}
                            elseif($dadcate->visivel=="5"){$visi = "Sexta";}
                            elseif($dadcate->visivel=="6"){$visi = "Sábado";}
                            elseif($dadcate->visivel=="0"){$visi = "Domingo";}

                            $stu = ($dadcate->status == "1") ? "Ativo" : "Bloqueado";
                        ?>
                        <tr>
                            <th><img src="../img/fotos_produtos/<?php echo $dadcate->foto ? $dadcate->foto : "off.jpg"; ?>" width="40"/></th>
                            <td><?php echo $buscat->nome; ?></td>
                            <td><?php echo $dadcate->nome; ?></td>
                            <td><?php echo $visi; ?></td>
                            <td><?php echo $stu; ?></td>
                            <td><a href="exibirdias.php?idpp=<?php echo $dadcate->id; ?>"><button class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Dias de Exibição"><i class="fa fa-calendar"></i></button></a></td>
                            <td align="center">
                                <?php if($dadcate->status=="1"){ ?>
                                <a href="produtos.php?desativar=<?php echo $dadcate->id; ?>"><button class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Desativar Produto"><i class="fa fa-eye"></i></button></a>
                                <?php } else { ?>
                                <a href="produtos.php?ativar=<?echo $dadcate->id; ?>"><button class="btn btn-success btn-sm" data-toggle="tooltip" data-placement="top" title="Ativar Produto"><i class="fa fa-eye"></i></button></a>
                                <?php } ?>
                            </td>
                            <td align="center">
                                <a href="variacoes.php?idpp=<?php echo $dadcate->id; ?>"><button class="btn btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Cadastrar Tamanhos"><i class="fa fa-braille" aria-hidden="true"></i></button></a>
                            </td>
                            <td align="center">
                                <a href="opgrupo.php?idpp=<?php echo $dadcate->id; ?>"><button class="btn btn-warning btn-sm" data-toggle="tooltip" data-placement="top" title="Cadastrar Opcionais"><i class="fa fa-plus-square"></i></button></a>
                            </td>
                            <td align="center">
                                <form action="editarproduto.php" method="post">
                                    <input type="hidden" name="codigoc" value="<?php echo $dadcate->id; ?>"/>
                                    <button type="submit" class="btn btn-purple btn-sm" data-toggle="tooltip" data-placement="top" title="Editar Produto"><i class="icon fa fa-pencil"></i></button>
                                </form>
                            </td>
                            <td align="center">
                                <form action="" method="post">
                                    <input type="hidden" name="deletarproduto" value="<?php echo $dadcate->id; ?>"/>
                                    <button type="submit" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Excluir Produto"><i class="icon fa fa-times"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div><!-- table-wrapper -->
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