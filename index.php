<?php
require_once 'config.php';

// Verificar se o usuário está logado (cliente ou fornecedor)
$isLoggedIn = isset($_SESSION['user_id']) || isset($_SESSION['fornecedor_id']);
$userType = '';
if (isset($_SESSION['user_id'])) {
    $userType = 'cliente';
} elseif (isset($_SESSION['fornecedor_id'])) {
    $userType = 'fornecedor';
}

// Obter produtos em destaque
$conn = getDBConnection();
$destaques = [];
$sql = "SELECT p.*, f.nome_fazenda, c.nome as categoria_nome 
        FROM produtos p 
        JOIN fornecedores f ON p.fornecedor_id = f.id 
        JOIN categorias c ON p.categoria_id = c.id 
        ORDER BY p.data_cadastro DESC LIMIT 4";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $destaques[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroDelivery - Plataforma de Vendas Agrícolas</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #4CAF50 0%, #8BC34A 100%);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .dashboard-card {
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: scale(1.03);
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
                <nav class="hidden md:flex space-x-6">
                    <a href="#" class="tab-link font-medium hover:text-green-100 active" data-tab="home">Início</a>
                    <a href="#" class="tab-link font-medium hover:text-green-100" data-tab="products">Produtos</a>
                    <?php if ($userType === 'fornecedor'): ?>
                        <a href="#" class="tab-link font-medium hover:text-green-100" data-tab="supplier">Fornecedor</a>
                    <?php endif; ?>
                    <a href="#" class="tab-link font-medium hover:text-green-100" data-tab="sales">Vendas</a>
                    <?php if ($userType === 'fornecedor'): ?>
                        <a href="#" class="tab-link font-medium hover:text-green-100" data-tab="dashboard">Dashboard</a>
                    <?php endif; ?>
                    <?php if ($isLoggedIn): ?>
                        <a href="logout.php" class="font-medium hover:text-green-100">Sair</a>
                    <?php else: ?>
                        <a href="login.php" class="font-medium hover:text-green-100">Login</a>
                    <?php endif; ?>
                </nav>
                <button class="md:hidden text-2xl" id="mobile-menu-button">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div class="md:hidden hidden bg-green-700 pb-4" id="mobile-menu">
            <div class="container mx-auto px-4 flex flex-col space-y-3">
                <a href="#" class="tab-link py-2 font-medium hover:text-green-100 active" data-tab="home">Início</a>
                <a href="#" class="tab-link py-2 font-medium hover:text-green-100" data-tab="products">Produtos</a>
                <?php if ($userType === 'fornecedor'): ?>
                    <a href="#" class="tab-link py-2 font-medium hover:text-green-100" data-tab="supplier">Fornecedor</a>
                <?php endif; ?>
                <a href="#" class="tab-link py-2 font-medium hover:text-green-100" data-tab="sales">Vendas</a>
                <?php if ($userType === 'fornecedor'): ?>
                    <a href="#" class="tab-link py-2 font-medium hover:text-green-100" data-tab="dashboard">Dashboard</a>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <a href="logout.php" class="py-2 font-medium hover:text-green-100">Sair</a>
                <?php else: ?>
                    <a href="login.php" class="py-2 font-medium hover:text-green-100">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Home Tab -->
        <div id="home" class="tab-content active">
            <section class="mb-12">
                <div class="gradient-bg rounded-xl text-white p-8 md:p-12">
                    <h2 class="text-3xl md:text-4xl font-bold mb-4">Conectando produtores rurais ao mercado</h2>
                    <p class="text-lg mb-6 max-w-2xl">Plataforma completa para venda e distribuição de produtos agrícolas direto do produtor para o consumidor final.</p>
                    <div class="flex flex-wrap gap-4">
                        <?php if (!$isLoggedIn): ?>
                            <a href="register.php" class="bg-white text-green-700 font-bold px-6 py-3 rounded-lg hover:bg-gray-100 transition">
                                Cadastre-se <i class="fas fa-arrow-right ml-2"></i>
                            </a>
                        <?php endif; ?>
                        <button class="border-2 border-white text-white font-bold px-6 py-3 rounded-lg hover:bg-green-600 transition">
                            Saiba mais
                        </button>
                    </div>
                </div>
            </section>

            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Como funciona</h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <div class="bg-green-100 text-green-700 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-user-plus text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">1. Cadastro do Fornecedor</h3>
                        <p class="text-gray-600">Produtores se cadastram na plataforma e adicionam seus produtos.</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <div class="bg-green-100 text-green-700 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-store text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">2. Venda Online</h3>
                        <p class="text-gray-600">Clientes compram produtos diretamente dos produtores com diversas formas de pagamento.</p>
                    </div>
                    <div class="bg-white p-6 rounded-xl shadow-md">
                        <div class="bg-green-100 text-green-700 w-12 h-12 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-truck text-xl"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2">3. Entrega e Gestão</h3>
                        <p class="text-gray-600">Logística de entrega e acompanhamento completo das vendas.</p>
                    </div>
                </div>
            </section>

            <section class="mb-12">
                <h2 class="text-2xl font-bold mb-6 text-gray-800">Destaques da semana</h2>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($destaques as $produto): ?>
                        <div class="bg-white rounded-xl shadow-md overflow-hidden product-card transition duration-300">
                            <img src="<?php echo $produto['imagem'] ?: 'https://via.placeholder.com/500x300?text=Produto+Sem+Imagem'; ?>" alt="<?php echo htmlspecialchars($produto['nome']); ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h3 class="font-bold text-lg"><?php echo htmlspecialchars($produto['nome']); ?></h3>
                                <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($produto['nome_fazenda']); ?></p>
                                <div class="flex justify-between items-center">
                                    <span class="font-bold text-green-700"><?php echo formatPrice($produto['preco']); ?>/<?php echo $produto['unidade_medida']; ?></span>
                                    <button class="bg-green-600 text-white px-3 py-1 rounded-md text-sm hover:bg-green-700 add-to-cart" data-id="<?php echo $produto['id']; ?>">Comprar</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <!-- Products Tab -->
        <div id="products" class="tab-content">
            <!-- Conteúdo será carregado via AJAX -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-green-600"></i>
                <p class="mt-4">Carregando produtos...</p>
            </div>
        </div>

        <!-- Supplier Tab -->
        <?php if ($userType === 'fornecedor'): ?>
        <div id="supplier" class="tab-content">
            <!-- Conteúdo será carregado via AJAX -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-green-600"></i>
                <p class="mt-4">Carregando área do fornecedor...</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Sales Tab -->
        <div id="sales" class="tab-content">
            <!-- Conteúdo será carregado via AJAX -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-green-600"></i>
                <p class="mt-4">Carregando carrinho de compras...</p>
            </div>
        </div>

        <!-- Dashboard Tab -->
        <?php if ($userType === 'fornecedor'): ?>
        <div id="dashboard" class="tab-content">
            <!-- Conteúdo será carregado via AJAX -->
            <div class="text-center py-8">
                <i class="fas fa-spinner fa-spin text-4xl text-green-600"></i>
                <p class="mt-4">Carregando dashboard...</p>
            </div>
        </div>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4 flex items-center">
                        <i class="fas fa-tractor mr-2"></i> AgroDelivery
                    </h3>
                    <p class="text-gray-400">Conectando produtores rurais diretamente aos consumidores, garantindo produtos frescos e de qualidade.</p>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4">Links Úteis</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="text-gray-400 hover:text-white">Sobre nós</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Como funciona</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Termos de uso</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Política de privacidade</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4">Contato</h4>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-center"><i class="fas fa-map-marker-alt mr-2"></i> BA 161, KM )*</li>
                        <li class="flex items-center"><i class="fas fa-phone mr-2"></i> (74) 981009327</li>
                        <li class="flex items-center"><i class="fas fa-envelope mr-2"></i> agrodeliveryceep@gmail.com</li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold mb-4">Redes Sociais</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="bg-gray-700 w-10 h-10 rounded-full flex items-center justify-center hover:bg-green-600">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="bg-gray-700 w-10 h-10 rounded-full flex items-center justify-center hover:bg-green-600">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="bg-gray-700 w-10 h-10 rounded-full flex items-center justify-center hover:bg-green-600">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://wa.me/+5574999824450" class="bg-gray-700 w-10 h-10 rounded-full flex items-center justify-center hover:bg-green-600">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> AgroDelivery. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });

        // Tab switching
        const tabLinks = document.querySelectorAll('.tab-link');
        const tabContents = document.querySelectorAll('.tab-content');

        tabLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all tabs
                tabLinks.forEach(l => l.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.classList.remove('active');
                });
                
                // Show the selected tab content
                const tabId = this.getAttribute('data-tab');
                const tabContent = document.getElementById(tabId);
                tabContent.classList.add('active');
                
                // Load content via AJAX if not already loaded
                if (tabContent.innerHTML.includes('Carregando')) {
                    loadTabContent(tabId);
                }
                
                // Close mobile menu if open
                document.getElementById('mobile-menu').classList.add('hidden');
            });
        });

        // Load tab content via AJAX
        function loadTabContent(tabId) {
            const tabContent = document.getElementById(tabId);
            const xhr = new XMLHttpRequest();
            
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    tabContent.innerHTML = xhr.responseText;
                    
                    // Initialize any scripts needed for the loaded content
                    if (tabId === 'products') {
                        initProductsPage();
                    } else if (tabId === 'supplier') {
                        initSupplierPage();
                    } else if (tabId === 'sales') {
                        initSalesPage();
                    } else if (tabId === 'dashboard') {
                        initDashboardPage();
                    }
                }
            };
            
            xhr.open('GET', `load_${tabId}.php`, true);
            xhr.send();
        }

        // Add to cart functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-cart')) {
                e.preventDefault();
                const productId = e.target.getAttribute('data-id');
                addToCart(productId);
            }
        });

        function addToCart(productId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'add_to_cart.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Produto adicionado ao carrinho!');
                        updateCartCount(response.cartCount);
                    } else {
                        alert(response.message || 'Erro ao adicionar produto ao carrinho');
                    }
                }
            };
            xhr.send(`product_id=${productId}`);
        }

        function updateCartCount(count) {
            const cartCountElement = document.getElementById('cart-count');
            if (cartCountElement) {
                cartCountElement.textContent = count;
            }
        }

        // Initialize functions for each tab
        function initProductsPage() {
            // Add product filtering and pagination functionality
            console.log('Products page initialized');
        }

        function initSupplierPage() {
            // Add supplier form submission and product management
            console.log('Supplier page initialized');
            
            // Form submissions
            document.getElementById('supplier-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Cadastro de fornecedor enviado com sucesso!');
                this.reset();
            });

            document.getElementById('product-form')?.addEventListener('submit', function(e) {
                e.preventDefault();
                alert('Produto cadastrado com sucesso!');
                this.reset();
            });
        }

        function initSalesPage() {
            // Add cart management and checkout functionality
            console.log('Sales page initialized');
            
            // Payment method toggle
            const paymentCredit = document.getElementById('payment-credit');
            const creditCardForm = document.getElementById('credit-card-form');

            if (paymentCredit && creditCardForm) {
                paymentCredit.addEventListener('change', function() {
                    if(this.checked) {
                        creditCardForm.classList.remove('hidden');
                    } else {
                        creditCardForm.classList.add('hidden');
                    }
                });
            }
        }

        function initDashboardPage() {
            // Initialize charts and dashboard widgets
            console.log('Dashboard page initialized');
            
            if(document.getElementById('supplierChart')) {
                const supplierCtx = document.getElementById('supplierChart').getContext('2d');
                const supplierChart = new Chart(supplierCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Fazenda Feliz', 'Sítio do Vale', 'Chácara Nova Era', 'Granja São José'],
                        datasets: [{
                            label: 'Vendas em R$',
                            data: [5320.50, 3150.20, 2980.10, 1000.00],
                            backgroundColor: [
                                'rgba(76, 175, 80, 0.7)',
                                'rgba(139, 195, 74, 0.7)',
                                'rgba(104, 159, 56, 0.7)',
                                'rgba(67, 160, 71, 0.7)'
                            ],
                            borderColor: [
                                'rgba(76, 175, 80, 1)',
                                'rgba(139, 195, 74, 1)',
                                'rgba(104, 159, 56, 1)',
                                'rgba(67, 160, 71, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                const paymentCtx = document.getElementById('paymentChart').getContext('2d');
                const paymentChart = new Chart(paymentCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['PIX', 'Cartão de Crédito', 'Cartão de Débito'],
                        datasets: [{
                            data: [65, 25, 10],
                            backgroundColor: [
                                'rgba(76, 175, 80, 0.7)',
                                'rgba(54, 162, 235, 0.7)',
                                'rgba(255, 206, 86, 0.7)'
                            ],
                            borderColor: [
                                'rgba(76, 175, 80, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false
                    }
                });
            }
        }
    </script>
</body>
</html>