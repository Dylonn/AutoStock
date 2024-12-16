<?php
session_start();
require_once 'configF.php';

if (!isset($_SESSION['email'])) {
    header("Location: tela1.php");
    exit();
}

// Conexão com o banco
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Obter o email do usuário logado
$email_usuario = $_SESSION['email'];

// Buscar o ID do usuário logado
$sql_usuario = "SELECT id_usuario FROM usuarios WHERE email = ?";
$stmt_usuario = $conn->prepare($sql_usuario);
$stmt_usuario->bind_param("s", $email_usuario);
$stmt_usuario->execute();
$result_usuario = $stmt_usuario->get_result();

if ($result_usuario->num_rows === 0) {
    die("Usuário não encontrado.");
}

$usuario = $result_usuario->fetch_assoc();
$id_usuario = $usuario['id_usuario'];
$stmt_usuario->close();

// Verifica se o caixa está aberto
$sql_caixa = "SELECT id_caixa FROM caixa WHERE id_usuario = ? AND status = 'aberto'";
$stmt_caixa = $conn->prepare($sql_caixa);
$stmt_caixa->bind_param("i", $id_usuario);
$stmt_caixa->execute();
$result_caixa = $stmt_caixa->get_result();

if ($result_caixa->num_rows === 0) {
    die("Caixa não está aberto. Abra o caixa para realizar vendas.");
}

$caixa = $result_caixa->fetch_assoc();
$id_caixa = $caixa['id_caixa'];
$stmt_caixa->close();

// Processa a venda ao submeter o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['realizar_venda'])) {
    $forma_pagamento = $_POST['forma_pagamento'];
    $itens = $_POST['itens']; // Deve conter array com tipo (produto/servico), id_item, preco, desconto

    // Insere a venda principal
    $sql_venda = "INSERT INTO venda (forma_pagamento, id_usuario, id_caixa) VALUES (?, ?, ?)";
    $stmt_venda = $conn->prepare($sql_venda);
    $stmt_venda->bind_param("sii", $forma_pagamento, $id_usuario, $id_caixa);
    $stmt_venda->execute();
    $id_venda = $stmt_venda->insert_id; // ID da venda criada
    $stmt_venda->close();

    // Inserir itens associados à venda
    $sql_itens = "INSERT INTO itens_venda (id_venda, tipo, id_item, preco, desconto) VALUES (?, ?, ?, ?, ?)";
    $stmt_itens = $conn->prepare($sql_itens);

    foreach ($itens as $item) {
        $tipo = $item['tipo']; // 'produto' ou 'servico'
        $id_item = $item['id_item'];
        $preco = $item['preco'];
        $desconto = $item['desconto'];

        $stmt_itens->bind_param("isidd", $id_venda, $tipo, $id_item, $preco, $desconto);
        $stmt_itens->execute();
    }

    $stmt_itens->close();
    echo "Venda realizada com sucesso!";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Realizar Venda</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background-color: #c9d6ff;
            background: linear-gradient(to right, #e2e2e2, #c9d6ff);
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            overflow-y: auto;
        }

        .container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            padding: 40px;
            width: 100%;
            max-width: 600px;
        }

        h1,h2 {
            text-align: center;
            color: #512da8;
            margin-bottom: 20px;
        }

        label {
            margin: 10px 0 5px;
            color: #333;
        }

        select,
        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
            background-color: #eee;
        }

        input[type="submit"],
        #botao-voltar {
            background-color: #512da8;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 10px;
            width: 100%;
            transition: background-color 0.3s;
            text-align: center;
            display: inline-block;
            text-decoration: none; /* Remove underline */
        }

        input[type="submit"]:hover,
        #botao-voltar:hover {
            background-color: #6a1b9a;
        }
        #botCadastroVoltar {
                display: inline-block;
                padding: 15px 30px;
                font-size: 18px;
                text-align: center;
                text-decoration: none;
                color: #fff;
                background-color: #512da8;
                border-radius: 5px;
                transition: background-color 0.3s ease;
                position: fixed;
                top: 3%;
                right: 90%;
        }

        #botCadastroVoltar:hover {
            background-color: #311b92;
        }
        
    </style>
</head>

<body>
<a href="tela3.php" id="botCadastroVoltar">Voltar</a>
    <div class="container">
        <h1>Realize sua Venda ou Serviço</h1>
        <h2><?php echo "Sessão iniciada com o valor de e-mail: " . ($_SESSION['email'] ?? "não definida."); ?></h2>
        <form id="vendaForm" action="processaVenda.php" method="POST">
            <label for="tipo">Tipo de Item:</label>
            <select name="tipo" id="tipo" required>
                <option value="produto">Produto</option>
                <option value="servico">Serviço</option>
            </select>

            <label for="search">Pesquisar Item:</label>
            <input type="text" id="search" placeholder="Digite o nome do item">

            <label for="item">Selecione o Item:</label>
            <select name="item" id="item" required>
                <!-- Options serão populadas via JavaScript -->
            </select>

            <p id="preco">Preço: R$ 0.00</p>

            <label for="desconto">Desconto:</label>
            <input type="number" name="desconto" id="desconto" step="0.01" min="0" required>

            <label for="pagamento">Forma de Pagamento:</label>
            <select name="pagamento" id="pagamento" required>
                <option value="pix">PIX</option>
                <option value="dinheiro">Dinheiro</option>
                <option value="debito">Cartão de Débito</option>
                <option value="credito">Cartão de Crédito</option>
            </select>

            <input type="submit" value="Realizar Venda">
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoSelect = document.getElementById('tipo');
            const itemSelect = document.getElementById('item');
            const precoP = document.getElementById('preco');
            const vendaForm = document.getElementById('vendaForm');
            const searchInput = document.getElementById('search');

            let allItems = [];

            function populateItems(tipo) {
                fetch(`getItems.php?tipo=${tipo}`)
                    .then(response => response.json())
                    .then(data => {
                        allItems = data;
                        filterItems();
                    });
            }

            function filterItems() {
                const searchTerm = searchInput.value.toLowerCase();
                const filteredItems = allItems.filter(item => item.nome.toLowerCase().includes(searchTerm));

                itemSelect.innerHTML = '<option value="">Selecione o Item</option>';
                if (filteredItems.length > 0) {
                    filteredItems.forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = item.nome;
                        itemSelect.appendChild(option);
                    });
                }
            }

            function updatePrice() {
                const itemId = itemSelect.value;
                if (itemId) {
                    fetch(`getPrice.php?id=${itemId}&tipo=${tipoSelect.value}`)
                        .then(response => response.json())
                        .then(data => {
                            precoP.textContent = `Preço: R$ ${data.preco}`;
                        });
                } else {
                    precoP.textContent = 'Preço: R$ 0.00';
                }
            }

            tipoSelect.addEventListener('change', function () {
                populateItems(this.value);
            });

            itemSelect.addEventListener('change', updatePrice);

            searchInput.addEventListener('input', filterItems);

            // Carregar produtos por padrão
            populateItems(tipoSelect.value);

            vendaForm.addEventListener('submit', function (event) {
                event.preventDefault(); // Impede o envio padrão do formulário
                const formData = new FormData(vendaForm);

                fetch('processaVenda.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(`${data.message}\nData e Hora da Venda: ${data.timestamp}`);
                            vendaForm.reset(); // Reseta o formulário
                            populateItems(tipoSelect.value); // Recarregar itens
                            precoP.textContent = 'Preço: R$ 0.00'; // Resetar preço
                        } else {
                            alert(`Erro: ${data.message}`);
                        }
                    });
            });
        });
    </script>
</body>

</html>
