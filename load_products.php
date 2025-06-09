<?php
require_once 'config.php';

// Obter parâmetros de filtro
$category = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 8;
$offset = ($page - 1) * $perPage;

// Construir consulta SQL
$conn = getDBConnection();
$sql = "SELECT p.*, f.nome_fazenda, c.nome as categoria_nome 
        FROM produtos p 
        JOIN fornecedores f ON p.fornecedor_id = f.id 
        JOIN categorias c ON p.categoria_id = c.id";

if (!empty($category)) {
    $sql .= " WHERE c.nome = ?";
}

$sql .= " LIMIT ? OFFSET ?";

// Preparar e executar a consulta
$stmt = $conn->prepare($sql);
if (!empty($category)) {
    $stmt->bind_param("sii", $category, $perPage, $offset);
} else {
    $stmt->bind_param("ii", $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Obter produtos
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Obter total de produtos para paginação
$countSql = "SELECT COUNT(*) as total FROM produtos";
if (!empty($category)) {
    $countSql .= " JOIN categorias ON produtos.categoria_id = categorias.id WHERE categorias.nome = ?";
}

$countStmt = $conn->prepare($countSql);
if (!empty($category)) {
    $countStmt->bind_param("s", $category);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);

// Obter categorias para o filtro
$categories = [];
$catSql = "SELECT * FROM categorias";
$catResult = $conn->query($catSql);
while ($row = $catResult->fetch_assoc()) {
    $categories[] = $row;
}

$conn->close();
?>

<section class="mb-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Nossos Produtos</h2>
        <div class="relative">
            <select id="category-filter" class="block appearance-none bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded leading-tight focus:outline-none focus:border-green-500">
                <option value="">Filtrar por categoria</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['nome']; ?>" <?php echo $cat['nome'] === $category ? 'selected' : ''; ?>>
                        <?php echo $cat['nome']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <i class="fas fa-chevron-down"></i>
            </div>
        </div>
    </div>

    <?php if (empty($products)): ?>
        <div class="bg-white rounded-xl shadow-md p-8 text-center">
            <i class="fas fa-box-open text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-xl font-bold text-gray-700 mb-2">Nenhum produto encontrado</h3>
            <p class="text-gray-600">Não encontramos produtos nesta categoria.</p>
        </div>
    <?php else: ?>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($products as $produto): ?>
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

        <div class="mt-8 flex justify-center">
            <nav class="inline-flex rounded-md shadow">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" class="px-3 py-2 rounded-l-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" class="<?php echo $i === $page ? 'z-10 bg-green-50 border-green-500 text-green-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $category ? '&category=' . urlencode($category) : ''; ?>" class="px-3 py-2 rounded-r-md border border-gray-300 bg-white text-gray-500 hover:bg-gray-50">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    <?php endif; ?>
</section>

<script>
    // Filter products by category
    document.getElementById('category-filter').addEventListener('change', function() {
        const category = this.value;
        const url = category ? `?category=${encodeURIComponent(category)}` : '';
        window.history.pushState({}, '', url);
        
        // Reload products with the new filter
        const tabContent = document.getElementById('products');
        const xhr = new