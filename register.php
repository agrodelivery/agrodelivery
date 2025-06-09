<?php
require_once 'config.php';

// Verificar se o usuário já está logado
if (isset($_SESSION['user_id']) || isset($_SESSION['fornecedor_id'])) {
    redirect('index.php');
}

// Variáveis para armazenar mensagens e dados do formulário
$error = '';
$success = '';
$formData = [
    'tipo' => '',
    'nome' => '',
    'nome_fazenda' => '',
    'email' => '',
    'telefone' => '',
    'cpf_cnpj' => '',
    'endereco' => '',
    'bio' => ''
];

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar os dados de entrada
    $formData['tipo'] = sanitizeInput($_POST['tipo'] ?? '');
    $formData['nome'] = sanitizeInput($_POST['nome'] ?? '');
    $formData['nome_fazenda'] = sanitizeInput($_POST['nome_fazenda'] ?? '');
    $formData['email'] = sanitizeInput($_POST['email'] ?? '');
    $formData['telefone'] = sanitizeInput($_POST['telefone'] ?? '');
    $formData['cpf_cnpj'] = sanitizeInput($_POST['cpf_cnpj'] ?? '');
    $formData['endereco'] = sanitizeInput($_POST['endereco'] ?? '');
    $formData['bio'] = sanitizeInput($_POST['bio'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Validações básicas
    if (empty($formData['tipo'])) {
        $error = 'Selecione o tipo de cadastro (Cliente ou Fornecedor)';
    } elseif (empty($formData['nome'])) {
        $error = 'O campo nome é obrigatório';
    } elseif (empty($formData['email'])) {
        $error = 'O campo email é obrigatório';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido';
    } elseif (empty($senha)) {
        $error = 'O campo senha é obrigatório';
    } elseif (strlen($senha) < 6) {
        $error = 'A senha deve ter pelo menos 6 caracteres';
    } elseif ($senha !== $confirmar_senha) {
        $error = 'As senhas não coincidem';
    } elseif ($formData['tipo'] === 'fornecedor' && empty($formData['cpf_cnpj'])) {
        $error = 'CPF/CNPJ é obrigatório para fornecedores';
    } else {
        $conn = getDBConnection();
        
        // Verificar se o email já está cadastrado
        $email_check = $conn->prepare("SELECT id FROM " . ($formData['tipo'] === 'cliente' ? 'clientes' : 'fornecedores') . " WHERE email = ?");
        $email_check->bind_param("s", $formData['email']);
        $email_check->execute();
        $email_check->store_result();
        
        if ($email_check->num_rows > 0) {
            $error = 'Este email já está cadastrado';
        } else {
            // Verificar CPF/CNPJ único para fornecedores
            if ($formData['tipo'] === 'fornecedor') {
                $cpf_cnpj_check = $conn->prepare("SELECT id FROM fornecedores WHERE cpf_cnpj = ?");
                $cpf_cnpj_check->bind_param("s", $formData['cpf_cnpj']);
                $cpf_cnpj_check->execute();
                $cpf_cnpj_check->store_result();
                
                if ($cpf_cnpj_check->num_rows > 0) {
                    $error = 'Este CPF/CNPJ já está cadastrado';
                }
            }
            
            if (empty($error)) {
                // Hash da senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Inserir no banco de dados
                if ($formData['tipo'] === 'cliente') {
                    $stmt = $conn->prepare("INSERT INTO clientes (nome, email, telefone, senha) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $formData['nome'], $formData['email'], $formData['telefone'], $senha_hash);
                } else {
                    $stmt = $conn->prepare("INSERT INTO fornecedores (nome_fazenda, cpf_cnpj, email, telefone, endereco, senha, bio) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $formData['nome_fazenda'], $formData['cpf_cnpj'], $formData['email'], $formData['telefone'], $formData['endereco'], $senha_hash, $formData['bio']);
                }
                
                if ($stmt->execute()) {
                    $success = 'Cadastro realizado com sucesso!';
                    
                    // Limpar o formulário
                    $formData = [
                        'tipo' => '',
                        'nome' => '',
                        'nome_fazenda' => '',
                        'email' => '',
                        'telefone' => '',
                        'cpf_cnpj' => '',
                        'endereco' => '',
                        'bio' => ''
                    ];
                    
                    // Redirecionar para login após 3 segundos
                    header("refresh:3;url=login.php");
                } else {
                    $error = 'Erro ao cadastrar: ' . $conn->error;
                }
            }
        }
        
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - AgroDelivery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
        }
        .form-container {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
    <!-- Header -->
    <header class="gradient-bg text-white shadow-lg">
        <div class="container mx-auto px-4 py-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-tractor text-3xl"></i>
                    <h1 class="text-2xl font-bold">AgroDelivery</h1>
                </div>
                <nav>
                    <a href="index.php" class="font-medium hover:text-green-100">Voltar para a página inicial</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white rounded-xl form-container overflow-hidden">
                <div class="gradient-bg text-white p-6 text-center">
                    <h2 class="text-2xl font-bold">Crie sua conta</h2>
                    <p class="mt-2">Junte-se à nossa plataforma de vendas agrícolas</p>
                </div>
                
                <div class="p-6 md:p-8">
                    <?php if ($error): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            <i class="fas fa-check-circle mr-2"></i>
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form id="register-form" method="POST" action="register.php">
                        <!-- Tipo de Cadastro -->
                        <div class="mb-6">
                            <label class="block text-gray-700 font-bold mb-2">Você é:</label>
                            <div class="flex flex-wrap gap-4">
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio text-green-600" name="tipo" value="cliente" <?php echo ($formData['tipo'] === 'cliente' ? 'checked' : ''); ?>>
                                    <span class="ml-2">Cliente</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio text-green-600" name="tipo" value="fornecedor" <?php echo ($formData['tipo'] === 'fornecedor' ? 'checked' : ''); ?>>
                                    <span class="ml-2">Fornecedor</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Dados Básicos -->
                        <div class="grid md:grid-cols-2 gap-6">
                            <div>
                                <label for="nome" class="block text-gray-700 font-bold mb-2">Nome Completo</label>
                                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($formData['nome']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            </div>
                            
                            <div id="fornecedor-field" class="<?php echo ($formData['tipo'] !== 'fornecedor' ? 'hidden' : ''); ?>">
                                <label for="nome_fazenda" class="block text-gray-700 font-bold mb-2">Nome da Fazenda/Propriedade</label>
                                <input type="text" id="nome_fazenda" name="nome_fazenda" value="<?php echo htmlspecialchars($formData['nome_fazenda']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" <?php echo ($formData['tipo'] === 'fornecedor' ? 'required' : ''); ?>>
                            </div>
                            
                            <div>
                                <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            </div>
                            
                            <div>
                                <label for="telefone" class="block text-gray-700 font-bold mb-2">Telefone</label>
                                <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($formData['telefone']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            </div>
                            
                            <div id="cpf-cnpj-field" class="<?php echo ($formData['tipo'] !== 'fornecedor' ? 'hidden' : ''); ?>">
                                <label for="cpf_cnpj" class="block text-gray-700 font-bold mb-2">CPF/CNPJ</label>
                                <input type="text" id="cpf_cnpj" name="cpf_cnpj" value="<?php echo htmlspecialchars($formData['cpf_cnpj']); ?>" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" <?php echo ($formData['tipo'] === 'fornecedor' ? 'required' : ''); ?>>
                            </div>
                            
                            <div>
                                <label for="senha" class="block text-gray-700 font-bold mb-2">Senha</label>
                                <input type="password" id="senha" name="senha" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            </div>
                            
                            <div>
                                <label for="confirmar_senha" class="block text-gray-700 font-bold mb-2">Confirmar Senha</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500" required>
                            </div>
                        </div>
                        
                        <!-- Campos específicos para fornecedores -->
                        <div id="fornecedor-fields" class="<?php echo ($formData['tipo'] !== 'fornecedor' ? 'hidden' : ''); ?> mt-6">
                            <div class="mb-6">
                                <label for="endereco" class="block text-gray-700 font-bold mb-2">Endereço Completo</label>
                                <textarea id="endereco" name="endereco" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"><?php echo htmlspecialchars($formData['endereco']); ?></textarea>
                            </div>
                            
                            <div>
                                <label for="bio" class="block text-gray-700 font-bold mb-2">Sobre sua produção</label>
                                <textarea id="bio" name="bio" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"><?php echo htmlspecialchars($formData['bio']); ?></textarea>
                                <p class="text-gray-500 text-sm mt-1">Conte um pouco sobre sua fazenda e métodos de produção</p>
                            </div>
                        </div>
                        
                        <div class="mt-8">
                            <button type="submit" class="gradient-bg text-white font-bold px-6 py-3 rounded-lg hover:bg-green-600 transition w-full">
                                Cadastrar <i class="fas fa-user-plus ml-2"></i>
                            </button>
                        </div>
                        
                        <div class="mt-4 text-center">
                            <p class="text-gray-600">Já tem uma conta? <a href="login.php" class="text-green-600 hover:text-green-800 font-medium">Faça login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-12">
        <div class="container mx-auto px-4">
            <div class="text-center">
                <h3 class="text-xl font-bold mb-4 flex items-center justify-center">
                    <i class="fas fa-tractor mr-2"></i> AgroDelivery
                </h3>
                <p class="text-gray-400 max-w-2xl mx-auto">Conectando produtores rurais diretamente aos consumidores, garantindo produtos frescos e de qualidade.</p>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> AgroDelivery. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mostrar/ocultar campos específicos para fornecedores
        document.querySelectorAll('input[name="tipo"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const isFornecedor = this.value === 'fornecedor';
                
                document.getElementById('fornecedor-field').classList.toggle('hidden', !isFornecedor);
                document.getElementById('cpf-cnpj-field').classList.toggle('hidden', !isFornecedor);
                document.getElementById('fornecedor-fields').classList.toggle('hidden', !isFornecedor);
                
                // Tornar campos obrigatórios ou não
                document.getElementById('nome_fazenda').required = isFornecedor;
                document.getElementById('cpf_cnpj').required = isFornecedor;
            });
        });

        // Máscara para telefone
        document.getElementById('telefone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
            }
            if (value.length > 10) {
                value = value.replace(/(\d)(\d{4})$/, '$1-$2');
            }
            
            e.target.value = value;
        });

        // Máscara para CPF/CNPJ
        document.getElementById('cpf_cnpj').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            
            if (value.length <= 11) {
                // CPF
                if (value.length > 3) {
                    value = value.replace(/^(\d{3})(\d)/g, '$1.$2');
                }
                if (value.length > 6) {
                    value = value.replace(/^(\d{3})\.(\d{3})(\d)/g, '$1.$2.$3');
                }
                if (value.length > 9) {
                    value = value.replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/g, '$1.$2.$3-$4');
                }
                if (value.length > 14) {
                    value = value.substring(0, 14);
                }
            } else {
                // CNPJ
                if (value.length > 2) {
                    value = value.replace(/^(\d{2})(\d)/g, '$1.$2');
                }
                if (value.length > 6) {
                    value = value.replace(/^(\d{2})\.(\d{3})(\d)/g, '$1.$2.$3');
                }
                if (value.length > 10) {
                    value = value.replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/g, '$1.$2.$3/$4');
                }
                if (value.length > 15) {
                    value = value.replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/g, '$1.$2.$3/$4-$5');
                }
                if (value.length > 18) {
                    value = value.substring(0, 18);
                }
            }
            
            e.target.value = value;
        });
    </script>
</body>
</html>