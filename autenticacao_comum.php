<?php
session_start();
require_once 'conexao.php'; // Conexão com o banco de dados

$mensagemErro = ''; // Variável para armazenar mensagens de erro

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Captura os dados do formulário
    $nome_materno = isset($_POST['nome_materno']) ? trim($_POST['nome_materno']) : null;
    $data_nascimento = isset($_POST['data_nascimento']) ? trim($_POST['data_nascimento']) : null;
    $cep = isset($_POST['cep']) ? trim($_POST['cep']) : null;

    // Valida se todos os campos foram preenchidos
    if (!$nome_materno || !$data_nascimento || !$cep) {
        $mensagemErro = "Todos os campos são obrigatórios!";
    } else {
        try {
            // Conecta ao banco de dados
            $conn = conectaPDO();

            // Consulta SQL para verificar se o usuário existe
            $sql = "SELECT * FROM cadastro WHERE nome_materno = :nome_materno AND data_nascimento = :data_nascimento AND cep = :cep AND nivel_acesso = 'comum'";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nome_materno', $nome_materno, PDO::PARAM_STR);
            $stmt->bindParam(':data_nascimento', $data_nascimento, PDO::PARAM_STR);
            $stmt->bindParam(':cep', $cep, PDO::PARAM_STR);
            $stmt->execute();

            // Verifica se o usuário foi encontrado
            if ($stmt->rowCount() > 0) {
                // Recupera os dados do usuário
                $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

                // Armazena informações do usuário na sessão
                $_SESSION['usuario'] = $usuario['login'];
                $_SESSION['nivel_acesso'] = $usuario['nivel_acesso'];
                $_SESSION['usuario_id'] = $usuario['id_cad'];

                // Verificação do nivel_acesso
                if ($_SESSION['nivel_acesso'] === 'comum') {
                    header("Location: dashboard_comum.php");
                    exit();
                } else {
                    $mensagemErro = "Acesso negado: Você não tem permissões suficientes.";
                }
            } else {
                $mensagemErro = "Erro: Dados não encontrados ou usuário inválido.";
            }
        } catch (PDOException $e) {
            $mensagemErro = "Erro ao consultar o banco de dados: " . $e->getMessage();
        }
    }
}

// Limpa os valores enviados caso haja erro
if (!empty($mensagemErro)) {
    $_POST = []; // Limpa os valores submetidos
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="css/autee_comum.css" rel="stylesheet">
    <title>Login Comum</title>
</head>
<body>
    
<h2>Login de Usuário Comum</h2>
    

<!-- Exibe mensagem de erro -->
<div class="erro-mensagem">
 <?php if (!empty($mensagemErro)): ?>
    <p style="color: red;"><?= htmlspecialchars($mensagemErro); ?></p>
 <?php endif; ?>
</div>

<div class="form-container">
    <form id="autenticar" method="POST">
        <label for="nome_materno">Qual o nome da sua mãe?</label><br>
        <input type="text" id="nome_materno" placeholder="Digite Nome da Mãe" name="nome_materno" <?= !empty($mensagemErro) ? '' : 'value="' . htmlspecialchars($_POST['nome_materno'] ?? '') . '"' ?> required><br><br>

        <label for="data_nascimento">Qual a sua data de nascimento?</label><br>
        <input type="date" id="data_nascimento" name="data_nascimento" <?= !empty($mensagemErro) ? '' : 'value="' . htmlspecialchars($_POST['data_nascimento'] ?? '') . '"' ?> required><br><br>

        <label for="cep">Qual o seu CEP?</label><br>
        <input type="text" id="cep" placeholder="Digite o CEP" name="cep" <?= !empty($mensagemErro) ? '' : 'value="' . htmlspecialchars($_POST['cep'] ?? '') . '"' ?> required><br><br>

        <input type="submit" value="Entrar">
        <input type="reset" value="Limpar">

    </form>
</div>

</body>
</html>
