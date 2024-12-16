<?php
session_start();
require_once 'configF.php';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

if (!isset($_SESSION['id_cliente'])) {
    header("Location: loginCliente.php"); 
    exit();
}

echo "Bem-vindo, " . htmlspecialchars($_SESSION['nome_cliente']);

// Verifica se foi enviado um termo de pesquisa
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Modifica a consulta para buscar as empresas com o nome filtrado
$sql = "SELECT * FROM perfilEmpresa WHERE nome LIKE ? ORDER BY nome ASC";
$stmt = $conn->prepare($sql);
$searchTermLike = "%$searchTerm%";
$stmt->bind_param("s", $searchTermLike);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Empresas</title>
</head>
<style>
    /* Definindo as cores principais */
:root {
    --cor-roxa: #5e2a84;
    --cor-branca: #ffffff;
    --cor-cinza-claro: #f1f1f1;
    --cor-cinza-escuro: #333333;
    --cor-cinza-medium: #888888;
}

/* Resetando margens e padding para todos os elementos */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Corpo da página */
body {
    font-family: 'Arial', sans-serif;
    background-color: var(--cor-cinza-claro);
    color: var(--cor-cinza-escuro);
    line-height: 1.6;
    padding: 20px;
}

/* Cabeçalho */
h1 {
    color: var(--cor-roxa);
    text-align: center;
    margin-bottom: 20px;
    font-size: 2.5em;
    text-transform: uppercase;
}

/* Barra de pesquisa */
.search-bar {
    display: flex;
    justify-content: center;
    margin-bottom: 20px;
}

.search-bar input[type="text"] {
    padding: 10px;
    font-size: 1.2em;
    width: 300px;
    border: 2px solid var(--cor-roxa);
    border-radius: 8px;
    outline: none;
}

.search-bar button {
    padding: 10px;
    font-size: 1.2em;
    margin-left: 10px;
    background-color: var(--cor-roxa);
    color: var(--cor-branca);
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.search-bar button:hover {
    background-color: #3a135d;
}

/* Lista de empresas */
ul {
    list-style-type: none;
    padding: 0;
    margin-top: 20px;
}

li {
    background-color: var(--cor-branca);
    margin: 10px 0;
    padding: 10px;
    border: 2px solid var(--cor-roxa);
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

li a {
    text-decoration: none;
    color: var(--cor-roxa);
    font-size: 1.5em;
    font-weight: bold;
    display: block;
}

li a:hover {
    color: #3a135d;
    text-decoration: underline;
}

/* Estilo para o link de "Voltar" */
a.voltar {
    display: inline-block;
    margin-top: 30px;
    padding: 10px 20px;
    background-color: var(--cor-roxa);
    color: var(--cor-branca);
    border-radius: 5px;
    font-size: 1.2em;
    text-align: center;
}

a.voltar:hover {
    background-color: #3a135d;
}

/* Estilos de responsividade */
@media (max-width: 768px) {
    h1 {
        font-size: 2em;
    }

    li {
        font-size: 1em;
        padding: 8px;
    }

    li a {
        font-size: 1.2em;
    }
}
</style>
<body>
<h1>Lista de Empresas</h1>

<!-- Barra de Pesquisa -->
<div class="search-bar">
    <form action="" method="get">
        <input type="text" name="search" placeholder="Pesquise pela empresa..." value="<?= htmlspecialchars($searchTerm) ?>">
        <button type="submit">Pesquisar</button>
    </form>
</div>

<!-- Exibição das Empresas -->
<ul>
    <?php if ($result->num_rows > 0): ?>
        <?php while ($empresa = $result->fetch_assoc()): ?>
            <li>
                <a href="MenuEmpresa.php?id=<?= $empresa['id'] ?>"><?= htmlspecialchars($empresa['nome']) ?></a>
            </li>
        <?php endwhile; ?>
    <?php else: ?>
        <li>Nenhuma empresa encontrada.</li>
    <?php endif; ?>
</ul>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
