<?php
$tabela = 'itens_venda';
require_once("../../../conexao.php");
@session_start();
$id_usuario = $_SESSION['id'];
$preco = @$_POST['preco'];
$preco = str_replace(',', '.', $preco);
$desconto = @$_POST['desconto'];
$troco = @$_POST['troco'];
$tipo_desconto = @$_POST['tipo_desconto'];
$frete = @$_POST['frete'];
$frete = str_replace(',', '.', $frete);

if ($frete == "") {
	$frete = 0;
}

if ($desconto == "") {
	$desconto = 0;
}

$total_troco = 0;
$total_trocoF = 0;

$total_v = 0;

//buscar o total da venda
$query = $pdo->query("SELECT * from $tabela where funcionario = '$id_usuario' and id_venda = '0' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$total_das_vendas = $res[$i]['total'];
		$total_v += $total_das_vendas;
	}
}

if ($tipo_desconto == '%') {
	if ($desconto > 0 and $total_v > 0) {
		$total_final = - ($total_v * $desconto / 100);
	} else {
		$total_final = 0;
	}
} else {
	$total_final = -$desconto;
}

$preco = floatval($preco);
$total_final = $total_final + $frete + $preco;


$query = $pdo->query("SELECT * from $tabela where funcionario = '$id_usuario' and id_venda = '0' order by id asc");
$res = $query->fetchAll(PDO::FETCH_ASSOC);
$linhas = @count($res);
echo '<div style="overflow:auto; max-height:200px; width:100%; scrollbar-width: thin;">';
if ($linhas > 0) {
	for ($i = 0; $i < $linhas; $i++) {
		$id = $res[$i]['id'];
		$material = $res[$i]['material'];
		$valor = $res[$i]['valor'];
		$quantidade = $res[$i]['quantidade'];
		$total = $res[$i]['total'];



		$total_final += $total;
		$total_finalF = number_format($total_final, 2, ',', '.');
		$valorF = number_format($valor, 2, ',', '.');
		$totalF = number_format($total, 2, ',', '.');

		if ($troco > 0) {
			$total_troco = $troco - $total_final;
			$total_trocoF = number_format($total_troco, 2, ',', '.');
		}


		$query2 = $pdo->query("SELECT * from materiais where id = '$material'");
		$res2 = $query2->fetchAll(PDO::FETCH_ASSOC);
		$nome_produto = $res2[0]['nome'];


		$ocultar_quantidades = '';
		$sigla_unidade = '';


		//tratamento separa string
		$qt = explode(".", $quantidade);
		if ($qt[1] > 0) {
			$quantidadeF = $quantidade;
		} else {
			$quantidadeF = $qt[0];
		}

		$nome_produtoF = mb_strimwidth($nome_produto, 0, 24, "...");

		echo '<div class="row">';
		echo '<div class="col-md-3" style="margin-right:3px">';
		echo '</div>';
		echo '<div class="col-md-9" style="margin-left:-15px; margin-top:3px">';
		echo '<span style="font-size:13px; margin-left: -15px">';
		echo '<span class="' . $ocultar_quantidades . '">' . $quantidadeF . '</span> ' . $nome_produtoF . ' ';
		echo '</span><br>';
		echo '<div style="font-size:12px; color:#570a03; margin-top:0px; margin-left:0px">
<a class="' . $ocultar_quantidades . '" href="#" onclick="diminuir(' . $id . ', ' . $quantidadeF . ')"><big><i class="fa fa-minus-circle text-danger" ></i></big></a>
' . $quantidadeF . ' ' . $sigla_unidade . '
<a class="' . $ocultar_quantidades . '" href="#" onclick="aumentar(' . $id . ', ' . $quantidadeF . ')"><big><i class="fa fa-plus-circle text-success" ></i></big></a>
';

		echo '<div class="dropdown head-dpdn2" style="position:absolute; top:0px; right:10px">
<a title="Remover Item" href="#" class="dropdown" data-bs-toggle="dropdown" aria-expanded="false"><big><i class="fa fa-times" style="color:#7d1107"></i></big></a>
<div class="dropdown-menu" style="margin-left:-50px;margin-top:-35px; background: #fcecd6">
    <div>
    <div class="notification_desc2" style="background: #fcecd6  ">
    <p style="font-size:12px; padding-top:10px; padding-left:10px">Remover Item? <a href="#" onclick="excluirItem(' . $id . ')"><span class="text-danger">Sim</span></a></p>
    </div>
    </div>										
</div>
</div>';

		// Certifique-se de que o valor esteja em formato numérico correto, substituindo a vírgula por ponto
		$valorF = str_replace(',', '.', $valorF);

		// Converte para float
		$valorF = (float) $valorF;

		// Formata o valor com 2 casas decimais
		$valorFFormatado = number_format($valorF, 2, '.', '');

		// Adicionando o input para definir o preço
		echo '<div style="margin-top:10px;">
    <label for="preco-produto-' . $id . '" style="font-size:12px;">Definir Preço:</label>
    <input type="number" id="preco-produto-' . $id . '" class="form-control input-preco-produto" data-id="' . $id . '" style="display:inline-block; width:100px; margin-left:5px;" step="0.01" value="' . $valorFFormatado . '">
</div>';


		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</div>';
	}
}
echo '</div>';

$total_finalF = number_format($total_final, 2, ',', '.');
echo '<div align="right" style="margin-top:10px; font-size:14px; border-top:1px solid #8f8f8f;" >';
echo '<br>';
echo '<span style="margin-right:40px;">Itens: <b>(' . $linhas . ')</b></span>';
echo '<span>Subtotal: </span>';
echo '<span style="font-weight:bold"> R$ ';
echo $total_finalF;
echo '</span>';
if ($troco > 0) {
	echo '<br><span>Troco: </span>';
	echo '<span style="font-weight:bold"> R$ ';
	echo $total_trocoF;
	echo '</span>';
}
echo '</div>';


?>

<script type="text/javascript">
	var itens = "<?= $linhas ?>";
	$('#valor_pago').val('<?= $total_final ?>')
	$('#subtotal_venda').val('<?= $total_final ?>')
	if (itens > 0) {
		$("#btn_limpar").show();
		$("#btn_venda").show();
	} else {
		$("#btn_limpar").hide();
		$("#btn_venda").hide();
	}

	function excluirItem(id) {
		$.ajax({
			url: 'paginas/' + pag + "/excluir-item.php",
			method: 'POST',
			data: {
				id
			},
			dataType: "html",

			success: function(mensagem) {
				if (mensagem.trim() == "Excluído com Sucesso") {
					listarVendas();
				} else {
					$('#mensagem-excluir').addClass('text-danger')
					$('#mensagem-excluir').text(mensagem)
				}
			}
		});
	}

	function diminuir(id, quantidade) {
		$.ajax({
			url: 'paginas/' + pag + "/diminuir.php",
			method: 'POST',
			data: {
				id,
				quantidade
			},
			dataType: "html",

			success: function(mensagem) {
				if (mensagem.trim() == "Excluído com Sucesso") {
					listarVendas();
				} else {
					$('#mensagem-excluir').addClass('text-danger')
					$('#mensagem-excluir').text(mensagem)
				}
			}
		});
	}


	function aumentar(id, quantidade) {
		$.ajax({
			url: 'paginas/' + pag + "/aumentar.php",
			method: 'POST',
			data: {
				id,
				quantidade
			},
			dataType: "html",

			success: function(mensagem) {

				if (mensagem.trim() == "Excluído com Sucesso") {
					listarVendas();
				} else {
					alert(mensagem)
					$('#mensagem-excluir').addClass('text-danger')
					$('#mensagem-excluir').text(mensagem)
				}
			}
		});
	}
	$(document).ready(function() {
		// Evento para capturar a entrada de valores nos inputs de preço
		$('.input-preco-produto').on('blur', function() {
			var id = $(this).data('id'); // ID do produto
			var preco = $(this).val(); // Valor do input

			// Verifica se o valor é válido
			if (preco !== "" && !isNaN(preco)) {
				// Atualiza o preço do produto no servidor
				atualizarPreco(id, preco);
			}
		});

		// Função para enviar o preço atualizado ao servidor
		function atualizarPreco(id, preco) {
			$.ajax({
				url: 'paginas/' + pag + "/atualizar-preco.php",
				method: 'POST',
				data: {
					id: id,
					preco: preco
				},
				success: function(response) {
					if (response.trim() === "Atualizado com Sucesso") {
						listarVendas(); // Recarrega a lista de vendas para atualizar o subtotal
					} else {
						// Exibe mensagem de erro se a atualização falhar
						alert('Erro ao atualizar o preço: ' + response);
					}
				},
				error: function(xhr, status, error) {
					// Trata erros de requisição Ajax
					alert('Ocorreu um erro na comunicação com o servidor: ' + error);
				}
			});
		}
	});
</script>