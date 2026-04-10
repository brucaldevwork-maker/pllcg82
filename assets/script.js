let cart = JSON.parse(localStorage.getItem('cart')) || [];
let user = JSON.parse(localStorage.getItem('user')) || { name: 'User', email: 'user@example.com' };
let isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';

const products = [
    {
        id: 1,
        name: 'PLLC Raven E-Bike',
        price: 25999,
        category: 'ebikes',
        subcategory: 'raven',
        image: 'image/raven.jpg',
        rating: 4.8,
        reviews: 124,
        description: 'Premium electric bicycle with long-lasting battery',
        stock: 15,
        minStock: 5,
        isAvailable: true,
        sku: 'PLLC-RVN-001'
    },
    {
        id: 2,
        name: 'PLLC Echo E-Bike',
        price: 22999,
        category: 'ebikes',
        subcategory: 'echo',
        image: 'image/echo v1.jpg',
        rating: 4.7,
        reviews: 98,
        description: 'Echo model with advanced features',
        stock: 8,
        minStock: 3,
        isAvailable: true,
        sku: 'PLLC-ECH-002'
    },
    {
        id: 3,
        name: 'PLLC Supreme E-Bike',
        price: 28999,
        category: 'ebikes',
        subcategory: 'supreme',
        image: 'image/supreme v1.jpg',
        rating: 4.9,
        reviews: 156,
        description: 'Top-tier electric bicycle with premium components',
        stock: 3,
        minStock: 2,
        isAvailable: true,
        sku: 'PLLC-SUP-003'
    },
    {
        id: 4,
        name: 'PLLC Skye E-Bike',
        price: 19999,
        category: 'ebikes',
        subcategory: 'skye',
        image: 'image/skye2.0.jpg',
        rating: 4.6,
        reviews: 87,
        description: 'Lightweight and efficient electric bike',
        stock: 12,
        minStock: 4,
        isAvailable: true,
        sku: 'PLLC-SKY-004'
    },
    {
        id: 5,
        name: 'PLLC Zhi 18 E-Bike',
        price: 17999,
        category: 'ebikes',
        subcategory: 'zhi18',
        image: 'image/zhi-018 modified.jpg',
        rating: 4.5,
        reviews: 73,
        description: 'Compact and reliable electric bicycle',
        stock: 10,
        minStock: 3,
        isAvailable: true,
        sku: 'PLLC-ZHI-005'
    },
    {
        id: 19,
        name: 'PLLC Adventure Plus E-Bike',
        price: 23999,
        category: 'ebikes',
        subcategory: 'adventure',
        image: 'image/adventure plus.jpg',
        rating: 4.7,
        reviews: 89,
        description: 'Adventure-ready electric bike for off-road terrain',
        stock: 6,
        minStock: 2,
        isAvailable: true,
        sku: 'PLLC-ADV-006'
    },
    {
        id: 20,
        name: 'PLLC Blaze E-Bike',
        price: 21999,
        category: 'ebikes',
        subcategory: 'blaze',
        image: 'image/blaze.jpg',
        rating: 4.6,
        reviews: 67,
        description: 'High-performance electric bike with sporty design',
        stock: 9,
        minStock: 3,
        isAvailable: true,
        sku: 'PLLC-BLZ-007'
    },
    {
        id: 21,
        name: 'PLLC DC V3 E-Bike',
        price: 24999,
        category: 'ebikes',
        subcategory: 'dc',
        image: 'image/dc v3.jpg',
        rating: 4.8,
        reviews: 112,
        description: 'Direct current powered electric bicycle',
        stock: 7,
        minStock: 2,
        isAvailable: true,
        sku: 'PLLC-DC-008'
    },
    {
        id: 22,
        name: 'PLLC Dusk V1 E-Bike',
        price: 19999,
        category: 'ebikes',
        subcategory: 'dusk',
        image: 'image/dusk v1.jpg',
        rating: 4.5,
        reviews: 78,
        description: 'Evening ride optimized electric bike',
        stock: 11,
        minStock: 4,
        isAvailable: true,
        sku: 'PLLC-DSK-009'
    },
    {
        id: 23,
        name: 'PLLC Mini Cargo V2',
        price: 18999,
        category: 'ebikes',
        subcategory: 'cargo',
        image: 'image/mini cargo v.2.jpg',
        rating: 4.4,
        reviews: 54,
        description: 'Compact cargo electric bike for urban delivery',
        stock: 13,
        minStock: 5,
        isAvailable: true,
        sku: 'PLLC-CRG-010'
    },
    {
        id: 24,
        name: 'PLLC P1 Plus V1',
        price: 27999,
        category: 'ebikes',
        subcategory: 'p1plus',
        image: 'image/p1 plua v.1.jpg',
        rating: 4.9,
        reviews: 134,
        description: 'Premium plus model with enhanced features',
        stock: 4,
        minStock: 2,
        isAvailable: true,
        sku: 'PLLC-P1P-011'
    },
    {
        id: 25,
        name: 'PLLC Pau V3.2',
        price: 20999,
        category: 'ebikes',
        subcategory: 'pau',
        image: 'image/pau v3.2.jpg',
        rating: 4.6,
        reviews: 91,
        description: 'Version 3.2 with improved battery life',
        stock: 8,
        minStock: 3,
        isAvailable: true,
        sku: 'PLLC-PAU-012'
    },
    {
        id: 26,
        name: 'PLLC Ragnar E-Bike',
        price: 26999,
        category: 'ebikes',
        subcategory: 'ragnar',
        image: 'image/ragnar.jpg',
        rating: 4.8,
        reviews: 156,
        description: 'Warrior-class electric bike for tough terrain',
        stock: 5,
        minStock: 2,
        isAvailable: true,
        sku: 'PLLC-RAG-013'
    },
    {
        id: 27,
        name: 'PLLC Storm V1',
        price: 22999,
        category: 'ebikes',
        subcategory: 'storm',
        image: 'image/storm v1.jpg',
        rating: 4.7,
        reviews: 103,
        description: 'Storm-ready electric bike for all weather',
        stock: 7,
        minStock: 3,
        isAvailable: true,
        sku: 'PLLC-STM-014'
    },
    {
        id: 28,
        name: 'PLLC Summer E-Bike',
        price: 17999,
        category: 'ebikes',
        subcategory: 'summer',
        image: 'image/summer.jpg',
        rating: 4.5,
        reviews: 67,
        description: 'Summer cruising electric bike',
        stock: 14,
        minStock: 5,
        isAvailable: true,
        sku: 'PLLC-SUM-015'
    },
    {
        id: 29,
        name: 'PLLC Supreme Plus V2',
        price: 32999,
        category: 'ebikes',
        subcategory: 'supremeplus',
        image: 'image/supreme plus v2.jpg',
        rating: 5.0,
        reviews: 89,
        description: 'Ultimate premium electric bike with all features',
        stock: 2,
        minStock: 1,
        isAvailable: true,
        sku: 'PLLC-SPP-016'
    },
    {
        id: 32,
        name: 'PLLC Motor Type 2.0',
        price: 4500,
        category: 'spareparts',
        subcategory: 'motor',
        image: 'image/motortype 2.0.jpg',
        rating: 4.9,
        reviews: 145,
        description: 'Advanced motor system with improved efficiency',
        stock: 15,
        minStock: 6,
        isAvailable: true,
        sku: 'PLLC-MOT-018'
    },
    {
        id: 33,
        name: 'PLLC Adjustable Seat',
        price: 1500,
        category: 'spareparts',
        subcategory: 'accessories',
        image: 'image/adjustable.jpg',
        rating: 4.6,
        reviews: 198,
        description: 'Comfortable adjustable seat for long rides',
        stock: 40,
        minStock: 15,
        isAvailable: true,
        sku: 'PLLC-ACC-019'
    },
    {
        id: 34,
        name: 'PLLC Pau 001 Model',
        price: 23999,
        category: 'ebikes',
        subcategory: 'pau',
        image: 'image/pau 001.jpg',
        rating: 4.7,
        reviews: 112,
        description: 'Special edition Pau 001 electric bike',
        stock: 6,
        minStock: 2,
        isAvailable: true,
        sku: 'PLLC-PAU-020'
    },
    {
        id: 6,
        name: 'PLLC 48V Long-Life Battery',
        price: 8500,
        category: 'batteries',
        subcategory: '48v',
        image: 'image/battery.avif',
        rating: 4.9,
        reviews: 89,
        description: '48V 20Ah high-capacity battery',
        stock: 25,
        minStock: 8,
        isAvailable: true,
        sku: 'PLLC-BAT-48V-001'
    },
    {
        id: 7,
        name: 'PLLC 60V High-Capacity Battery',
        price: 12000,
        category: 'batteries',
        subcategory: '60v',
        image: 'image/battery.jpg',
        rating: 4.8,
        reviews: 67,
        description: '60V 25Ah premium battery pack',
        stock: 15,
        minStock: 5,
        isAvailable: true,
        sku: 'PLLC-BAT-60V-002'
    },
    {
        id: 8,
        name: 'PLLC Lithium Pro Battery',
        price: 9500,
        category: 'batteries',
        subcategory: 'lithium',
        image: 'image/battery.avif',
        rating: 4.7,
        reviews: 54,
        description: 'Advanced lithium-ion battery technology',
        stock: 20,
        minStock: 6,
        isAvailable: true,
        sku: 'PLLC-BAT-LITH-003'
    },
    {
        id: 9,
        name: 'PLLC Standard Battery',
        price: 5500,
        category: 'batteries',
        subcategory: 'standard',
        image: 'image/battery.jpg',
        rating: 4.5,
        reviews: 76,
        description: '36V 15Ah standard battery pack',
        stock: 30,
        minStock: 10,
        isAvailable: true,
        sku: 'PLLC-BAT-STD-004'
    },
    {
        id: 10,
        name: 'PLLC Heavy Duty Battery',
        price: 15000,
        category: 'batteries',
        subcategory: 'heavy-duty',
        image: 'image/battery.avif',
        rating: 4.8,
        reviews: 43,
        description: 'Industrial-grade battery for heavy usage',
        stock: 8,
        minStock: 3,
        isAvailable: true,
        sku: 'PLLC-BAT-HD-005'
    },
    {
        id: 11,
        name: 'PLLC Tire Set (Front/Rear)',
        price: 2500,
        category: 'spareparts',
        subcategory: 'tires',
        image: 'image/spareparts.jpg',
        rating: 4.6,
        reviews: 67,
        description: 'High-quality tire set for e-bikes',
        stock: 40,
        minStock: 12,
        isAvailable: true,
        sku: 'PLLC-SP-TIRE-001'
    },
    {
        id: 12,
        name: 'PLLC Brake Lever Assembly',
        price: 1200,
        category: 'spareparts',
        subcategory: 'brakes',
        image: 'image/spareparts.jpg',
        rating: 4.5,
        reviews: 45,
        description: 'Complete brake lever assembly',
        stock: 35,
        minStock: 10,
        isAvailable: true,
        sku: 'PLLC-SP-BRAKE-002'
    },
    {
        id: 13,
        name: 'PLLC Motor Controller',
        price: 3500,
        category: 'spareparts',
        subcategory: 'motor',
        image: 'image/motortype.jpg',
        rating: 4.7,
        reviews: 38,
        description: 'Advanced motor controller for e-bikes',
        stock: 15,
        minStock: 5,
        isAvailable: true,
        sku: 'PLLC-SP-MOTOR-003'
    },
    {
        id: 14,
        name: 'PLLC LED Headlight Unit',
        price: 800,
        category: 'spareparts',
        subcategory: 'lighting',
        image: 'image/spareparts.jpg',
        rating: 4.4,
        reviews: 52,
        description: 'Bright LED headlight for night riding',
        stock: 50,
        minStock: 15,
        isAvailable: true,
        sku: 'PLLC-SP-LED-004'
    },
    {
        id: 15,
        name: 'PLLC Throttle Grip',
        price: 600,
        category: 'spareparts',
        subcategory: 'accessories',
        image: 'image/spareparts.jpg',
        rating: 4.3,
        reviews: 41,
        description: 'Ergonomic throttle grip for comfortable riding',
        stock: 45,
        minStock: 12,
        isAvailable: true,
        sku: 'PLLC-SP-THROTTLE-005'
    },
    {
        id: 16,
        name: 'PLLC Charger Port Cover',
        price: 300,
        category: 'spareparts',
        subcategory: 'accessories',
        image: 'image/spareparts.jpg',
        rating: 4.2,
        reviews: 29,
        description: 'Protective cover for charging port',
        stock: 60,
        minStock: 20,
        isAvailable: true,
        sku: 'PLLC-SP-COVER-006'
    },
    {
        id: 17,
        name: 'PLLC Side Mirror (Pair)',
        price: 450,
        category: 'spareparts',
        subcategory: 'accessories',
        image: 'image/spareparts.jpg',
        rating: 4.4,
        reviews: 36,
        description: 'Pair of side mirrors for better visibility',
        stock: 30,
        minStock: 8,
        isAvailable: true,
        sku: 'PLLC-SP-MIRROR-007'
    },
    {
        id: 18,
        name: 'PLLC Brake Pads Set',
        price: 1250,
        category: 'spareparts',
        subcategory: 'brakes',
        image: 'image/spareparts.jpg',
        rating: 4.6,
        reviews: 67,
        description: 'High-quality brake pads for PLLC e-bikes',
        stock: 25,
        minStock: 8,
        isAvailable: true,
        sku: 'PLLC-SP-PADS-008'
    }
];

function formatPrice(price) {
    return `₱${price.toLocaleString()}`;
}

function updateCartCount() {
    const cartCountElements = document.querySelectorAll('#cartCount');
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    cartCountElements.forEach(element => {
        element.textContent = totalItems;
    });
}

function saveCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart(productId, quantity = 1) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({
            id: productId,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: quantity
        });
    }
    
    saveCart();
    updateCartCount();
    showNotification(`${product.name} added to cart!`);
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    saveCart();
    updateCartCount();
    loadCartItems();
}

function updateCartQuantity(productId, quantity) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        if (quantity <= 0) {
            removeFromCart(productId);
        } else {
            item.quantity = quantity;
            saveCart();
            updateCartCount();
            loadCartItems();
        }
    }
}

function showNotification(message) {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: #4CAF50;
        color: white;
        padding: 15px 20px;
        border-radius: 4px;
        z-index: 10000;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        font-weight: bold;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (document.body.contains(notification)) {
        document.body.removeChild(notification);
        }
    }, 3000);
}

function login(username, password) {
    if (username === 'Admin' && password === 'Admin123') {
        user = { name: 'Admin User', email: 'admin@pllc.com' };
        isLoggedIn = true;
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('isLoggedIn', 'true');
        return true;
    } else if (username === 'Customer' && password === 'Customer123') {
        user = { name: 'Customer User', email: 'customer@pllc.com' };
        isLoggedIn = true;
        localStorage.setItem('user', JSON.stringify(user));
        localStorage.setItem('isLoggedIn', 'true');
        return true;
    }
    return false;
}

function logout() {
    user = { name: 'User', email: 'user@example.com' };
    isLoggedIn = false;
    localStorage.removeItem('user');
    localStorage.removeItem('isLoggedIn');
    cart = [];
    saveCart();
    updateCartCount();
    window.location.href = 'index.html';
}

function renderProduct(product) {
    return `
        <div class="product-card" data-category="${product.category}">
            <div class="product-image-container">
                <div class="product-badge ${product.rating >= 4.8 ? 'best-seller' : product.rating >= 4.6 ? 'new' : 'sale'}">${product.rating >= 4.8 ? 'Best Seller' : product.rating >= 4.6 ? 'New' : 'Sale'}</div>
                <img src="${product.image}" alt="${product.name}" class="product-image" onerror="this.src='https://via.placeholder.com/300x200/cccccc/666666?text=No+Image'">
            </div>
            <div class="product-info">
                <h4 class="product-title">${product.name}</h4>
                <p class="product-description">${product.description}</p>
                <div class="product-price">
                    <span>${formatPrice(product.price)}</span>
                    <span class="product-price-original">${formatPrice(Math.round(product.price * 1.2))}</span>
                    <span class="product-discount">-${Math.floor(Math.random() * 20) + 10}%</span>
                </div>
                <div class="product-rating">
                    <span class="stars">${'★'.repeat(Math.floor(product.rating))}${'☆'.repeat(5-Math.floor(product.rating))}</span>
                    <span class="rating-text">${product.rating} (${product.reviews})</span>
                </div>
                <div class="product-stock ${product.stock > product.minStock ? 'in-stock' : product.stock > 0 ? 'low-stock' : 'out-of-stock'}">${product.stock > product.minStock ? 'In Stock' : product.stock > 0 ? `Only ${product.stock} left` : 'Out of Stock'}</div>
                <button class="add-to-cart-btn" onclick="addToCart(${product.id})" ${product.stock === 0 ? 'disabled' : ''}>${product.stock === 0 ? 'Out of Stock' : 'Add to Cart'}</button>
            </div>
        </div>
    `;
}

function loadProducts(containerId, category = null, limit = null) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let filteredProducts = products;
    if (category) {
        filteredProducts = products.filter(p => p.category === category);
    }
    
    if (limit) {
        filteredProducts = filteredProducts.slice(0, limit);
    }

    container.innerHTML = filteredProducts.map(renderProduct).join('');
}

function loadCartItems() {
    const container = document.getElementById('cartItems');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <h2>Your cart is empty</h2>
                <p>Add some products to get started!</p>
                <a href="products.html" class="cta-button">Continue Shopping</a>
            </div>
        `;
        updateCartSummary();
        return;
    }

    container.innerHTML = cart.map(item => `
        <div class="cart-item">
            <img src="${item.image}" alt="${item.name}" class="cart-item-image" onerror="this.src='https://via.placeholder.com/120x120/cccccc/666666?text=No+Image'">
            <div class="cart-item-details">
                <h4 class="cart-item-title">${item.name}</h4>
                <p class="cart-item-price">${formatPrice(item.price)}</p>
                <div class="quantity-controls">
                    <button class="quantity-btn" onclick="updateCartQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <input type="number" class="quantity-input" value="${item.quantity}" min="1" onchange="updateCartQuantity(${item.id}, parseInt(this.value))">
                    <button class="quantity-btn" onclick="updateCartQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
                <button class="remove-item" onclick="removeFromCart(${item.id})">Remove</button>
            </div>
        </div>
    `).join('');

    updateCartSummary();
}

function updateCartSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const shipping = subtotal > 0 ? 200 : 0;
    const total = subtotal + shipping;

    const subtotalEl = document.getElementById('subtotal');
    const shippingEl = document.getElementById('shipping');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    const checkoutBtn = document.getElementById('checkoutBtn');

    if (subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
    if (shippingEl) shippingEl.textContent = formatPrice(shipping);
    if (taxEl) taxEl.textContent = formatPrice(0);
    if (totalEl) totalEl.textContent = formatPrice(total);
    if (checkoutBtn) {
        checkoutBtn.disabled = cart.length === 0;
        checkoutBtn.textContent = cart.length === 0 ? 'Cart is Empty' : 'Proceed to Checkout';
    }
}

function searchProducts(query) {
    const searchResults = products.filter(product => 
        product.name.toLowerCase().includes(query.toLowerCase()) ||
        product.description.toLowerCase().includes(query.toLowerCase()) ||
        product.category.toLowerCase().includes(query.toLowerCase())
    );
    return searchResults;
}

function filterProducts() {
    const checkboxes = document.querySelectorAll('.filter-option input[type="checkbox"]');
    const selectedCategories = Array.from(checkboxes)
        .filter(cb => cb.checked)
        .map(cb => cb.value);

    let filteredProducts = products;
    
    if (selectedCategories.length > 0) {
        filteredProducts = products.filter(product => 
            selectedCategories.includes(product.category) ||
            selectedCategories.some(cat => {
                if (cat === 'under-5000') return product.price < 5000;
                if (cat === '5000-15000') return product.price >= 5000 && product.price <= 15000;
                if (cat === '15000-30000') return product.price >= 15000 && product.price <= 30000;
                if (cat === 'over-30000') return product.price > 30000;
                return false;
            })
        );
    }

    const container = document.getElementById('productsGrid');
    if (container) {
        container.innerHTML = filteredProducts.map(renderProduct).join('');
    }
}

function sortProducts(sortBy) {
    let sortedProducts = [...products];
    
    switch(sortBy) {
        case 'price-low':
            sortedProducts.sort((a, b) => a.price - b.price);
            break;
        case 'price-high':
            sortedProducts.sort((a, b) => b.price - a.price);
            break;
        case 'rating':
            sortedProducts.sort((a, b) => b.rating - a.rating);
            break;
        case 'newest':
            // For demo, we'll sort by ID (higher ID = newer)
            sortedProducts.sort((a, b) => b.id - a.id);
            break;
        default:
            // Featured - keep original order
            break;
    }
    
    const container = document.getElementById('productsGrid');
    if (container) {
        container.innerHTML = sortedProducts.map(renderProduct).join('');
    }
}

function updateUserWelcome() {
    const userWelcomeElements = document.querySelectorAll('#userWelcome');
    userWelcomeElements.forEach(element => {
        element.textContent = isLoggedIn ? `Hello, ${user.name}` : 'Hello, Guest';
    });
}

function toggleUserDropdown() {
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
        dropdown.style.display = dropdown.classList.contains('show') ? 'block' : 'none';
    }
}

function closeUserDropdown() {
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown) {
        dropdown.classList.remove('show');
        dropdown.style.display = 'none';
    }
}

function togglePasswordVisibility(inputId, buttonId) {
    const input = document.getElementById(inputId);
    const button = document.getElementById(buttonId);
    
    if (input && button) {
        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = '🙈';
        } else {
            input.type = 'password';
            button.textContent = '👁️';
        }
    }
}

function initializePasswordToggles() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const container = this.closest('.password-input-container');
            const input = container.querySelector('input[type="password"], input[type="text"]');
            
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.textContent = '🙈';
                } else {
                    input.type = 'password';
                    this.textContent = '👁️';
                }
            }
        });
    });
}

function checkPasswordStrength(password) {
    const strengthIndicator = document.getElementById('passwordStrength');
    if (!strengthIndicator) return;

    let strength = 0;
    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;

    strengthIndicator.className = 'password-strength';
    if (strength < 3) {
        strengthIndicator.classList.add('weak');
        strengthIndicator.textContent = 'Weak';
    } else if (strength < 5) {
        strengthIndicator.classList.add('medium');
        strengthIndicator.textContent = 'Medium';
    } else {
        strengthIndicator.classList.add('strong');
        strengthIndicator.textContent = 'Strong';
    }
}

function checkPasswordMatch() {
    const password = document.getElementById('newPassword')?.value;
    const confirmPassword = document.getElementById('confirmPassword')?.value;
    const matchIndicator = document.getElementById('passwordMatch');
    
    if (!matchIndicator) return;

    matchIndicator.className = 'password-match';
    if (confirmPassword) {
        if (password === confirmPassword) {
            matchIndicator.classList.add('match');
        } else {
            matchIndicator.classList.add('no-match');
        }
    }
}

function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;

    inputs.forEach(input => {
        const errorElement = input.parentNode.querySelector('.field-error');
        
        if (!input.value.trim()) {
            input.classList.add('error');
            input.classList.remove('success');
            if (errorElement) {
                errorElement.textContent = 'This field is required';
            }
            isValid = false;
        } else {
            input.classList.remove('error');
            input.classList.add('success');
            if (errorElement) {
                errorElement.textContent = '';
            }
        }
    });

    return isValid;
}

function initializePage() {
    updateUserWelcome();
    updateCartCount();
    initializeSearch();
    initializeUserDropdown();
    initializeForms();
    initializePasswordToggles();
    
    const currentPage = window.location.pathname.split('/').pop();
    initializePageSpecific(currentPage);
}

function initializeSearch() {
    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    if (searchInput && searchBtn) {
        const performSearch = () => {
            const query = searchInput.value.trim();
            if (query) {
                const results = searchProducts(query);
                if (window.location.pathname.includes('products.html')) {
                    const container = document.getElementById('productsGrid');
                    if (container) {
                        container.innerHTML = results.map(renderProduct).join('');
                    }
    } else {
                    window.location.href = `products.html?search=${encodeURIComponent(query)}`;
                }
            }
        };

        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }
}

function initializeUserDropdown() {
    const userDropdownBtn = document.querySelector('.user-dropdown-btn');
    if (userDropdownBtn) {
        userDropdownBtn.addEventListener('click', toggleUserDropdown);
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.user-menu')) {
            closeUserDropdown();
        }
    });
}

function initializeForms() {
    // Initialize all forms with validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
        });
    });
}

function validateField(field) {
    const errorElement = field.parentNode.querySelector('.field-error');
    
    if (!field.value.trim()) {
        field.classList.add('error');
        field.classList.remove('success');
        if (errorElement) {
            errorElement.textContent = 'This field is required';
        }
        return false;
    } else {
        field.classList.remove('error');
        field.classList.add('success');
        if (errorElement) {
            errorElement.textContent = '';
        }
    return true;
}
}

function initializePageSpecific(pageName) {
    switch(pageName) {
        case 'dashboard.html':
            loadProducts('featuredProducts', null, 6);
                    break;
        case 'products.html':
            loadProducts('productsGrid');
            handleSearchParameter();
                    break;
        case 'ebikes.html':
            loadProducts('productsGrid', 'ebikes');
                    break;
        case 'battery.html':
            loadProducts('productsGrid', 'batteries');
                    break;
        case 'spareparts.html':
            loadProducts('productsGrid', 'spareparts');
                    break;
        case 'cart.html':
            loadCartItems();
                        break;
        case 'checkout.html':
            loadCheckoutItems();
            initializeCheckout();
                        break;
        case 'transaction-history.html':
            loadOrders();
            initializeOrderFilters();
            break;
        case 'appointment.html':
            initializeAppointment();
            break;
        case 'feedback.html':
            initializeFeedback();
            break;
        case 'account.html':
            initializeAccount();
            break;
        case 'admin_login.html':
            initializeAdminLogin();
            break;
        case 'admin_dashboard.html':
            initializeAdminDashboard();
            break;
    }
}

function handleSearchParameter() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('search');
    if (searchQuery) {
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.value = searchQuery;
            const results = searchProducts(searchQuery);
            const container = document.getElementById('productsGrid');
            if (container) {
                container.innerHTML = results.map(renderProduct).join('');
            }
        }
    }
}

function initializeCheckout() {
    // Delivery type toggle
    const deliveryOptions = document.querySelectorAll('input[name="deliveryType"]');
    deliveryOptions.forEach(option => {
        option.addEventListener('change', function() {
            const deliveryAddress = document.getElementById('deliveryAddress');
            const pickupLocation = document.getElementById('pickupLocation');
            
            if (this.value === 'delivery') {
                deliveryAddress.style.display = 'block';
                pickupLocation.style.display = 'none';
            } else {
                deliveryAddress.style.display = 'none';
                pickupLocation.style.display = 'block';
            }
            updateCheckoutSummary();
        });
    });

    // Payment method toggle
    const paymentOptions = document.querySelectorAll('input[name="paymentMethod"]');
    paymentOptions.forEach(option => {
        option.addEventListener('change', function() {
            const gcashDetails = document.getElementById('gcashDetails');
            const bankDetails = document.getElementById('bankDetails');
            const codDetails = document.getElementById('codDetails');
            
            [gcashDetails, bankDetails, codDetails].forEach(el => {
                if (el) el.style.display = 'none';
            });
            
            if (this.value === 'gcash' && gcashDetails) {
                gcashDetails.style.display = 'block';
            } else if (this.value === 'bank' && bankDetails) {
                bankDetails.style.display = 'block';
            } else if (this.value === 'cod' && codDetails) {
                codDetails.style.display = 'block';
            }
        });
    });

    // Place order button
    const placeOrderBtn = document.getElementById('placeOrderBtn');
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', placeOrder);
    }
}

function initializeOrderFilters() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            filterButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.getAttribute('data-filter');
            loadOrders(filter);
        });
    });
}

function initializeAppointment() {
    const dateInput = document.getElementById('appointmentDate');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
    }

    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm('appointmentForm')) {
                const formData = {
                    customerName: document.getElementById('customerName').value,
                    customerEmail: document.getElementById('customerEmail').value,
                    customerPhone: document.getElementById('customerPhone').value,
                    bikeModel: document.getElementById('bikeModel').value,
                    serviceType: document.getElementById('serviceType').value,
                    appointmentDate: document.getElementById('appointmentDate').value,
                    appointmentTime: document.getElementById('appointmentTime').value,
                    branchLocation: document.getElementById('branchLocation').value,
                    problemDescription: document.getElementById('problemDescription').value,
                    urgency: document.getElementById('urgency').value
                };

                const appointment = bookAppointment(formData);
                showNotification(`Appointment booked successfully! Reference: ${appointment.id}`);
                appointmentForm.reset();
            }
        });
    }
}

function initializeFeedback() {
    const starInputs = document.querySelectorAll('.star-rating input');
    const ratingText = document.getElementById('ratingText');
    
    starInputs.forEach(input => {
        input.addEventListener('change', function() {
            const rating = parseInt(this.value);
            const ratingTexts = {
                1: 'Poor',
                2: 'Fair',
                3: 'Good',
                4: 'Very Good',
                5: 'Excellent'
            };
            if (ratingText) {
                ratingText.textContent = ratingTexts[rating];
            }
        });
    });

    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
        feedbackForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm('feedbackForm')) {
                const formData = {
                    customerName: document.getElementById('customerName').value,
                    customerEmail: document.getElementById('customerEmail').value,
                    feedbackType: document.getElementById('feedbackType').value,
                    rating: document.querySelector('input[name="rating"]:checked')?.value,
                    feedbackMessage: document.getElementById('feedbackMessage').value,
                    recommendation: document.getElementById('recommendation').value
                };

                const feedback = submitFeedback(formData);
                showNotification('Feedback submitted successfully!');
                
                const reactionBox = document.getElementById('reactionBox');
                if (reactionBox) {
                    reactionBox.style.display = 'block';
                    displaySentimentAnalysis(feedback.sentiment);
                }
                
                feedbackForm.reset();
                if (ratingText) {
                    ratingText.textContent = 'Please select a rating';
                }
            }
        });
    }
}

function initializeAccount() {
    const userNameDisplay = document.getElementById('userNameDisplay');
    const userEmailDisplay = document.getElementById('userEmailDisplay');
    const memberSinceDisplay = document.getElementById('memberSinceDisplay');
    
    if (userNameDisplay) userNameDisplay.textContent = user.name;
    if (userEmailDisplay) userEmailDisplay.textContent = user.email;
    if (memberSinceDisplay) memberSinceDisplay.textContent = 'Jan 2024';
    
    const profileForm = document.getElementById('profileUpdateForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm('profileUpdateForm')) {
                showNotification('Profile updated successfully!');
            }
        });
    }
    
    const passwordForm = document.getElementById('passwordChangeForm');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm('passwordChangeForm')) {
                showNotification('Password changed successfully!');
            }
        });
    }
}

function initializeAdminLogin() {
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('adminUsername').value;
            const password = document.getElementById('adminPassword').value;
            
            if (adminLogin(username, password)) {
                showNotification('Admin login successful!');
                setTimeout(() => {
                    window.location.href = 'admin_dashboard.html';
                }, 1000);
            } else {
                showNotification('Invalid admin credentials!');
            }
        });
    }
}

function initializeAdminDashboard() {
    if (localStorage.getItem('adminLoggedIn') !== 'true') {
        window.location.href = 'admin_login.html';
        return;
    }

    loadAdminDashboard();

    const adminNavButtons = document.querySelectorAll('.admin-nav-btn');
    adminNavButtons.forEach(button => {
        button.addEventListener('click', function() {
            adminNavButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const sectionId = this.getAttribute('data-section');
            const sections = document.querySelectorAll('.admin-section');
            sections.forEach(section => section.classList.remove('active'));
            document.getElementById(sectionId).classList.add('active');
        });
    });
    
    const adminLogoutBtn = document.getElementById('adminLogoutBtn');
    if (adminLogoutBtn) {
        adminLogoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout from admin panel?')) {
                adminLogout();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    updateUserWelcome();
    initializePage();


    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            
            if (login(username, password)) {
                showNotification('Login successful!');
                setTimeout(() => {
                    window.location.href = 'dashboard.html';
                }, 1000);
            } else {
                showNotification('Invalid username or password!');
            }
        });
    }

    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (validateForm('registerForm')) {
                showNotification('Registration successful! Please login.');
                setTimeout(() => {
                    document.querySelector('[data-tab="login"]').click();
                }, 1000);
            }
        });
    }

    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const inputId = this.id.replace('toggle', '').replace('New', 'new').replace('Confirm', 'confirm');
            togglePasswordVisibility(inputId, this.id);
        });
    });

    const newPasswordInput = document.getElementById('newPassword');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
        });
    }

    const confirmPasswordInput = document.getElementById('confirmPassword');
    if (confirmPasswordInput) {
        confirmPasswordInput.addEventListener('input', checkPasswordMatch);
    }

    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            tabButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            const tabs = document.querySelectorAll('.auth-tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            document.getElementById(targetTab + 'Tab').classList.add('active');
        });
    });

    const logoutButtons = document.querySelectorAll('#logoutBtn, .logout-page-btn');
    logoutButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to logout?')) {
                logout();
            }
        });
    });
    
    const logoutLinks = document.querySelectorAll('a[href="#"]');
    logoutLinks.forEach(link => {
        if (link.textContent.includes('Sign Out') || link.textContent.includes('Logout')) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to logout?')) {
                    logout();
                }
            });
        }
    });

    const searchInput = document.querySelector('.search-input');
    const searchBtn = document.querySelector('.search-btn');
    
    if (searchInput && searchBtn) {
        const performSearch = () => {
            const query = searchInput.value.trim();
            if (query) {
                const results = searchProducts(query);
                if (window.location.pathname.includes('products.html')) {
                    const container = document.getElementById('productsGrid');
                    if (container) {
                        container.innerHTML = results.map(renderProduct).join('');
                    }
                } else {
                    window.location.href = `products.html?search=${encodeURIComponent(query)}`;
                }
            }
        };

        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                performSearch();
            }
        });
    }

    const filterCheckboxes = document.querySelectorAll('.filter-option input[type="checkbox"]');
    filterCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', filterProducts);
    });

    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortProducts(this.value);
        });
    }

    const checkoutBtn = document.getElementById('checkoutBtn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            if (cart.length > 0) {
                window.location.href = 'checkout.html';
            }
        });
    }

    const currentPage = window.location.pathname.split('/').pop();
    
    switch(currentPage) {
        case 'dashboard.html':
            loadProducts('featuredProducts', null, 6);
            break;
        case 'products.html':
            loadProducts('productsGrid');
            // Handle search parameter
            const urlParams = new URLSearchParams(window.location.search);
            const searchQuery = urlParams.get('search');
            if (searchQuery) {
                searchInput.value = searchQuery;
                const results = searchProducts(searchQuery);
                const container = document.getElementById('productsGrid');
                if (container) {
                    container.innerHTML = results.map(renderProduct).join('');
                }
            }
            break;
        case 'ebikes.html':
            loadProducts('productsGrid', 'ebikes');
            break;
        case 'battery.html':
            loadProducts('productsGrid', 'batteries');
            break;
        case 'spareparts.html':
            loadProducts('productsGrid', 'spareparts');
            break;
        case 'cart.html':
            loadCartItems();
            break;
        case 'checkout.html':
            loadCheckoutItems();
            
            // Delivery type toggle
            const deliveryOptions = document.querySelectorAll('input[name="deliveryType"]');
            deliveryOptions.forEach(option => {
                option.addEventListener('change', function() {
                    const deliveryAddress = document.getElementById('deliveryAddress');
                    const pickupLocation = document.getElementById('pickupLocation');
                    
                    if (this.value === 'delivery') {
                        deliveryAddress.style.display = 'block';
                        pickupLocation.style.display = 'none';
                    } else {
                        deliveryAddress.style.display = 'none';
                        pickupLocation.style.display = 'block';
                    }
                    updateCheckoutSummary();
                });
            });

            // Payment method toggle
            const paymentOptions = document.querySelectorAll('input[name="paymentMethod"]');
            paymentOptions.forEach(option => {
                option.addEventListener('change', function() {
                    const gcashDetails = document.getElementById('gcashDetails');
                    const bankDetails = document.getElementById('bankDetails');
                    const codDetails = document.getElementById('codDetails');
                    
                    // Hide all payment details
                    [gcashDetails, bankDetails, codDetails].forEach(el => {
                        if (el) el.style.display = 'none';
                    });
                    
                    // Show selected payment details
                    if (this.value === 'gcash' && gcashDetails) {
                        gcashDetails.style.display = 'block';
                    } else if (this.value === 'bank' && bankDetails) {
                        bankDetails.style.display = 'block';
                    } else if (this.value === 'cod' && codDetails) {
                        codDetails.style.display = 'block';
                    }
                });
            });

            // Place order button
            const placeOrderBtn = document.getElementById('placeOrderBtn');
            if (placeOrderBtn) {
                placeOrderBtn.addEventListener('click', placeOrder);
            }
            break;
        case 'account.html':
            // Update user info display
            const userNameDisplay = document.getElementById('userNameDisplay');
            const userEmailDisplay = document.getElementById('userEmailDisplay');
            const memberSinceDisplay = document.getElementById('memberSinceDisplay');
            
            if (userNameDisplay) userNameDisplay.textContent = user.name;
            if (userEmailDisplay) userEmailDisplay.textContent = user.email;
            if (memberSinceDisplay) memberSinceDisplay.textContent = 'Jan 2024';
            
            // Profile update form
            const profileForm = document.getElementById('profileUpdateForm');
            if (profileForm) {
                profileForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (validateForm('profileUpdateForm')) {
                        showNotification('Profile updated successfully!');
                    }
                });
            }
            
            // Password change form
            const passwordForm = document.getElementById('passwordChangeForm');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (validateForm('passwordChangeForm')) {
                        showNotification('Password changed successfully!');
                    }
                });
            }
            break;
        case 'transaction-history.html':
            loadOrders();
            
            // Filter buttons
            const filterButtons = document.querySelectorAll('.filter-btn');
            filterButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active button
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Load filtered orders
                    const filter = this.getAttribute('data-filter');
                    loadOrders(filter);
                });
            });
            break;
        case 'appointment.html':
            // Set minimum date to today
            const dateInput = document.getElementById('appointmentDate');
            if (dateInput) {
                const today = new Date().toISOString().split('T')[0];
                dateInput.min = today;
            }

            // Appointment form
            const appointmentForm = document.getElementById('appointmentForm');
            if (appointmentForm) {
                appointmentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (validateForm('appointmentForm')) {
                        const formData = {
                            customerName: document.getElementById('customerName').value,
                            customerEmail: document.getElementById('customerEmail').value,
                            customerPhone: document.getElementById('customerPhone').value,
                            bikeModel: document.getElementById('bikeModel').value,
                            serviceType: document.getElementById('serviceType').value,
                            appointmentDate: document.getElementById('appointmentDate').value,
                            appointmentTime: document.getElementById('appointmentTime').value,
                            branchLocation: document.getElementById('branchLocation').value,
                            problemDescription: document.getElementById('problemDescription').value,
                            urgency: document.getElementById('urgency').value
                        };

                        const appointment = bookAppointment(formData);
                        showNotification(`Appointment booked successfully! Reference: ${appointment.id}`);
                        
                        // Reset form
                        appointmentForm.reset();
                    }
                });
            }
            break;
        case 'feedback.html':
            // Star rating functionality
            const starInputs = document.querySelectorAll('.star-rating input');
            const ratingText = document.getElementById('ratingText');
            
            starInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const rating = parseInt(this.value);
                    const ratingTexts = {
                        1: 'Poor',
                        2: 'Fair',
                        3: 'Good',
                        4: 'Very Good',
                        5: 'Excellent'
                    };
                    if (ratingText) {
                        ratingText.textContent = ratingTexts[rating];
            }
        });
    });

            // Feedback form
            const feedbackForm = document.getElementById('feedbackForm');
            if (feedbackForm) {
                feedbackForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (validateForm('feedbackForm')) {
                        const formData = {
                            customerName: document.getElementById('customerName').value,
                            customerEmail: document.getElementById('customerEmail').value,
                            feedbackType: document.getElementById('feedbackType').value,
                            rating: document.querySelector('input[name="rating"]:checked')?.value,
                            feedbackMessage: document.getElementById('feedbackMessage').value,
                            recommendation: document.getElementById('recommendation').value
                        };

                        const feedback = submitFeedback(formData);
                        showNotification('Feedback submitted successfully!');
                        
                        // Show reaction box with sentiment analysis
                        const reactionBox = document.getElementById('reactionBox');
                        if (reactionBox) {
                            reactionBox.style.display = 'block';
                            displaySentimentAnalysis(feedback.sentiment);
                        }
                        
                        // Reset form
                        feedbackForm.reset();
                        if (ratingText) {
                            ratingText.textContent = 'Please select a rating';
                        }
                    }
                });
            }
            break;
        case 'admin_login.html':
            // Admin login form
            const adminLoginForm = document.getElementById('adminLoginForm');
            if (adminLoginForm) {
                adminLoginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const username = document.getElementById('adminUsername').value;
                    const password = document.getElementById('adminPassword').value;
                    
                    if (adminLogin(username, password)) {
                        showNotification('Admin login successful!');
                        setTimeout(() => {
                            window.location.href = 'admin_dashboard.html';
                        }, 1000);
                    } else {
                        showNotification('Invalid admin credentials!');
                    }
                });
            }
            break;
        case 'admin_dashboard.html':
            // Check admin authentication
            if (localStorage.getItem('adminLoggedIn') !== 'true') {
                window.location.href = 'admin_login.html';
                return;
            }

            // Load admin dashboard
            loadAdminDashboard();

            // Admin navigation
            const adminNavButtons = document.querySelectorAll('.admin-nav-btn');
            adminNavButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Update active button
                    adminNavButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Show corresponding section
                    const sectionId = this.getAttribute('data-section');
                    const sections = document.querySelectorAll('.admin-section');
                    sections.forEach(section => section.classList.remove('active'));
                    document.getElementById(sectionId).classList.add('active');
        });
    });
    
            // Admin logout
            const adminLogoutBtn = document.getElementById('adminLogoutBtn');
            if (adminLogoutBtn) {
                adminLogoutBtn.addEventListener('click', function(e) {
    e.preventDefault();
        if (confirm('Are you sure you want to logout from admin panel?')) {
                        adminLogout();
                    }
                });
            }
            break;
    }

    // Appointment form logic
    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();

            // Collect form data
            const data = {
                name: document.getElementById('customerName').value.trim(),
                email: document.getElementById('customerEmail').value.trim(),
                phone: document.getElementById('customerPhone').value.trim(),
                bikeModel: document.getElementById('bikeModel').value,
                serviceType: document.getElementById('serviceType').value,
                appointmentDate: document.getElementById('appointmentDate').value,
                appointmentTime: document.getElementById('appointmentTime').value,
                branchLocation: document.getElementById('branchLocation').value,
                problemDescription: document.getElementById('problemDescription').value.trim(),
                urgency: document.getElementById('urgency').value
            };

            // Simple validation
            for (const key in data) {
                if (['name','email','phone','bikeModel','serviceType','appointmentDate','appointmentTime','branchLocation'].includes(key) && !data[key]) {
                    alert('Please fill out all required fields.');
                    return;
                }
            }

            // Simulate booking (replace with backend API call)
            alert(
                `Appointment booked!\n\n` +
                `Name: ${data.name}\nEmail: ${data.email}\nPhone: ${data.phone}\n` +
                `Bike Model: ${data.bikeModel}\nService Type: ${data.serviceType}\n` +
                `Date: ${data.appointmentDate}\nTime: ${data.appointmentTime}\nBranch: ${data.branchLocation}\n` +
                `Urgency: ${data.urgency}\nDescription: ${data.problemDescription || 'N/A'}`
            );

            appointmentForm.reset();
        });
    }

    // Header dropdown logic (for sign out)
    const userDropdownBtn = document.querySelector('.user-dropdown-btn');
    const userDropdown = document.querySelector('.user-dropdown');
    if (userDropdownBtn && userDropdown) {
        userDropdownBtn.addEventListener('click', () => {
            userDropdown.style.display = userDropdown.style.display === 'block' ? 'none' : 'block';
        });
        document.addEventListener('click', (event) => {
            if (!userDropdownBtn.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.style.display = 'none';
            }
        });
    }
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            alert('You have been signed out.');
            window.location.href = 'login.html';
        });
    }
});

function loadCheckoutItems() {
    const container = document.getElementById('orderItems');
    if (!container) return;

    if (cart.length === 0) {
        container.innerHTML = '<p>Your cart is empty. <a href="products.html">Continue Shopping</a></p>';
        return;
    }

    container.innerHTML = cart.map(item => `
        <div class="order-item">
            <img src="${item.image}" alt="${item.name}" class="item-image" onerror="this.src='https://via.placeholder.com/80x80/cccccc/666666?text=No+Image'">
            <div class="item-details">
                <h4>${item.name}</h4>
                <p>Quantity: ${item.quantity}</p>
                <p class="item-price">${formatPrice(item.price * item.quantity)}</p>
            </div>
        </div>
    `).join('');

    updateCheckoutSummary();
}

function updateCheckoutSummary() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryType = document.querySelector('input[name="deliveryType"]:checked')?.value;
    const shipping = deliveryType === 'delivery' ? 150 : 0;
    const total = subtotal + shipping;

    const subtotalEl = document.getElementById('subtotal');
    const shippingEl = document.getElementById('shipping');
    const taxEl = document.getElementById('tax');
    const totalEl = document.getElementById('total');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    if (subtotalEl) subtotalEl.textContent = formatPrice(subtotal);
    if (shippingEl) shippingEl.textContent = formatPrice(shipping);
    if (taxEl) taxEl.textContent = formatPrice(0);
    if (totalEl) totalEl.textContent = formatPrice(total);
    if (placeOrderBtn) {
        placeOrderBtn.disabled = cart.length === 0;
        placeOrderBtn.textContent = cart.length === 0 ? 'Cart is Empty' : 'Place Order';
    }
    
    // Update payment amounts
    const gcashAmount = document.getElementById('gcashAmount');
    const bankAmount = document.getElementById('bankAmount');
    const codAmount = document.getElementById('codAmount');
    
    if (gcashAmount) gcashAmount.textContent = formatPrice(total);
    if (bankAmount) bankAmount.textContent = formatPrice(total);
    if (codAmount) codAmount.textContent = formatPrice(total);
}

function placeOrder() {
    if (cart.length === 0) {
        showNotification('Your cart is empty!');
        return;
    }

    const deliveryType = document.querySelector('input[name="deliveryType"]:checked')?.value;
    const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked')?.value;
    
    if (!deliveryType || !paymentMethod) {
        showNotification('Please select delivery and payment options!');
        return;
    }

    // Validate required fields based on delivery type
    if (deliveryType === 'delivery') {
        const requiredFields = ['firstName', 'lastName', 'email', 'phone', 'address', 'city', 'postalCode'];
        const missingFields = requiredFields.filter(field => !document.getElementById(field)?.value.trim());
        
        if (missingFields.length > 0) {
            showNotification('Please fill in all required delivery information!');
            return;
        }
    } else if (deliveryType === 'pickup') {
        const pickupBranch = document.getElementById('pickupBranch')?.value;
        if (!pickupBranch) {
            showNotification('Please select a pickup branch!');
            return;
        }
    }

    // Generate order number
    const orderNumber = 'PLLC-' + Date.now();
    
    // Create order object
    const order = {
        orderNumber,
        date: new Date().toISOString(),
        items: [...cart],
        deliveryType,
        paymentMethod,
        status: 'pending',
        total: cart.reduce((sum, item) => sum + (item.price * item.quantity), 0) + 
               (deliveryType === 'delivery' ? 150 : 0)
    };

    // Save order to localStorage
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    orders.push(order);
    localStorage.setItem('orders', JSON.stringify(orders));

    // Clear cart
    cart = [];
    saveCart();
    updateCartCount();

    // Show success message
    showNotification(`Order placed successfully! Order #${orderNumber}`);
    
    // Redirect to order confirmation or dashboard
    setTimeout(() => {
    window.location.href = 'transaction-history.html';
    }, 2000);
}

function loadOrders(filter = 'all') {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const container = document.getElementById('ordersList');
    if (!container) return;

    let filteredOrders = orders;
    if (filter !== 'all') {
        filteredOrders = orders.filter(order => order.status === filter);
    }

    if (filteredOrders.length === 0) {
        container.innerHTML = `
            <div class="empty-cart">
                <h2>No orders found</h2>
                <p>You haven't placed any orders yet.</p>
                <a href="products.html" class="cta-button">Start Shopping</a>
                    </div>
        `;
        return;
    }

    // Sort orders by date (newest first)
    filteredOrders.sort((a, b) => new Date(b.date) - new Date(a.date));

    container.innerHTML = filteredOrders.map(order => `
        <div class="order-card">
            <div class="order-header">
                <div class="order-info">
                    <h3>Order #${order.orderNumber}</h3>
                    <p class="order-date">Placed on ${new Date(order.date).toLocaleDateString()}</p>
                    <p class="total-amount">${formatPrice(order.total)}</p>
                </div>
                <div class="order-status ${order.status}">${order.status.toUpperCase()}</div>
            </div>
            
            <div class="order-items">
                ${order.items.map(item => `
                    <div class="order-item">
                        <img src="${item.image}" alt="${item.name}" class="item-image" onerror="this.src='https://via.placeholder.com/80x80/cccccc/666666?text=No+Image'">
                        <div class="item-details">
                            <h4>${item.name}</h4>
                            <p>Quantity: ${item.quantity}</p>
                            <p class="item-price">${formatPrice(item.price * item.quantity)}</p>
                        </div>
                    </div>
                `).join('')}
            </div>
            
            <div class="order-actions">
                <button class="action-btn" onclick="viewOrderDetails('${order.orderNumber}')">View Details</button>
                ${order.status === 'pending' ? '<button class="action-btn" onclick="cancelOrder(\'' + order.orderNumber + '\')">Cancel Order</button>' : ''}
                ${order.status === 'delivered' ? '<button class="action-btn" onclick="reorderItems(\'' + order.orderNumber + '\')">Reorder</button>' : ''}
            </div>
        </div>
    `).join('');
}

function viewOrderDetails(orderNumber) {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const order = orders.find(o => o.orderNumber === orderNumber);
    
    if (!order) {
        showNotification('Order not found!');
                return;
            }
            
    const details = `
Order Number: ${order.orderNumber}
Date: ${new Date(order.date).toLocaleString()}
Status: ${order.status.toUpperCase()}
Delivery: ${order.deliveryType === 'delivery' ? 'Home Delivery' : 'Store Pickup'}
Payment: ${order.paymentMethod.toUpperCase()}
Total: ${formatPrice(order.total)}

Items:
${order.items.map(item => `- ${item.name} x${item.quantity} = ${formatPrice(item.price * item.quantity)}`).join('\n')}
    `;

    alert(details);
}

function cancelOrder(orderNumber) {
    if (!confirm('Are you sure you want to cancel this order?')) return;

    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const orderIndex = orders.findIndex(o => o.orderNumber === orderNumber);
    
    if (orderIndex !== -1) {
        orders[orderIndex].status = 'cancelled';
        localStorage.setItem('orders', JSON.stringify(orders));
        showNotification('Order cancelled successfully!');
        loadOrders(document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all');
    }
}

function reorderItems(orderNumber) {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const order = orders.find(o => o.orderNumber === orderNumber);
    
    if (!order) {
        showNotification('Order not found!');
        return;
    }

    // Add items to cart
    order.items.forEach(item => {
        addToCart(item.id, item.quantity);
    });

    showNotification('Items added to cart!');
    setTimeout(() => {
        window.location.href = 'cart.html';
    }, 1000);
}

function bookAppointment(formData) {
    const appointment = {
        id: 'APT-' + Date.now(),
        customerName: formData.customerName,
        customerEmail: formData.customerEmail,
        customerPhone: formData.customerPhone,
        bikeModel: formData.bikeModel,
        serviceType: formData.serviceType,
        appointmentDate: formData.appointmentDate,
        appointmentTime: formData.appointmentTime,
        branchLocation: formData.branchLocation,
        problemDescription: formData.problemDescription,
        urgency: formData.urgency,
        status: 'pending',
        createdAt: new Date().toISOString()
    };

    // Save appointment to localStorage
    const appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    appointments.push(appointment);
    localStorage.setItem('appointments', JSON.stringify(appointments));

    return appointment;
}

function submitFeedback(formData) {
    const feedback = {
        id: 'FB-' + Date.now(),
        customerName: formData.customerName,
        customerEmail: formData.customerEmail,
        feedbackType: formData.feedbackType,
        rating: parseInt(formData.rating),
        feedbackMessage: formData.feedbackMessage,
        recommendation: formData.recommendation,
        sentiment: analyzeSentiment(formData.feedbackMessage),
        createdAt: new Date().toISOString()
    };

    // Save feedback to localStorage
    const feedbacks = JSON.parse(localStorage.getItem('feedbacks')) || [];
    feedbacks.push(feedback);
    localStorage.setItem('feedbacks', JSON.stringify(feedbacks));

    return feedback;
}

function analyzeSentiment(text) {
    const positiveWords = ['good', 'great', 'excellent', 'amazing', 'wonderful', 'fantastic', 'love', 'perfect', 'best', 'awesome', 'outstanding', 'brilliant', 'superb', 'marvelous', 'delighted', 'satisfied', 'happy', 'pleased'];
    const negativeWords = ['bad', 'terrible', 'awful', 'horrible', 'disappointed', 'hate', 'worst', 'poor', 'unacceptable', 'frustrated', 'angry', 'annoyed', 'disgusted', 'displeased', 'unsatisfied', 'unhappy', 'upset'];
    
    const words = text.toLowerCase().split(/\s+/);
    let positiveCount = 0;
    let negativeCount = 0;
    
    words.forEach(word => {
        if (positiveWords.includes(word)) positiveCount++;
        if (negativeWords.includes(word)) negativeCount++;
    });
    
    if (positiveCount > negativeCount) return 'positive';
    if (negativeCount > positiveCount) return 'negative';
    return 'neutral';
}

function displaySentimentAnalysis(sentiment) {
    const container = document.getElementById('sentimentAnalysis');
    if (!container) return;
    
    const sentimentLabels = {
        positive: { label: 'Positive', class: 'positive' },
        negative: { label: 'Negative', class: 'negative' },
        neutral: { label: 'Neutral', class: 'neutral' }
    };

    const sentimentData = sentimentLabels[sentiment];
    const percentage = sentiment === 'positive' ? 85 : sentiment === 'negative' ? 15 : 50;

        container.innerHTML = `
        <div class="sentiment-indicator">
            <span class="sentiment-label">Sentiment Analysis:</span>
            <span class="sentiment-result ${sentimentData.class}">${sentimentData.label}</span>
        </div>
        <div class="sentiment-bar">
            <div class="sentiment-progress ${sentimentData.class}" style="width: ${percentage}%"></div>
            </div>
        `;
}

window.addToCart = addToCart;
window.removeFromCart = removeFromCart;
window.updateCartQuantity = updateCartQuantity;
window.togglePasswordVisibility = togglePasswordVisibility;
window.checkPasswordStrength = checkPasswordStrength;
window.checkPasswordMatch = checkPasswordMatch;
window.validateForm = validateForm;
window.filterProducts = filterProducts;
window.sortProducts = sortProducts;
window.placeOrder = placeOrder;
window.viewOrderDetails = viewOrderDetails;
window.cancelOrder = cancelOrder;
window.reorderItems = reorderItems;

function adminLogin(username, password) {
    if (username === 'Admin' && password === 'Admin123') {
        localStorage.setItem('adminLoggedIn', 'true');
        localStorage.setItem('adminUser', JSON.stringify({ name: 'Admin', role: 'admin' }));
        return true;
    }
    return false;
}

function adminLogout() {
    localStorage.removeItem('adminLoggedIn');
    localStorage.removeItem('adminUser');
    window.location.href = 'admin_login.html';
}

function loadAdminDashboard() {
    // Load statistics
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    const feedbacks = JSON.parse(localStorage.getItem('feedbacks')) || [];
    
    // Update stats
    document.getElementById('totalOrders').textContent = orders.length;
    document.getElementById('pendingAppointments').textContent = appointments.filter(apt => apt.status === 'pending').length;
    document.getElementById('totalFeedback').textContent = feedbacks.length;
    
    const avgRating = feedbacks.length > 0 ? 
        (feedbacks.reduce((sum, fb) => sum + fb.rating, 0) / feedbacks.length).toFixed(1) : '0.0';
    document.getElementById('averageRating').textContent = avgRating;
    
    // Load stock statistics
    const lowStockProducts = getLowStockProducts();
    const outOfStockProducts = getOutOfStockProducts();
    
    document.getElementById('lowStockItems').textContent = lowStockProducts.length;
    document.getElementById('outOfStockItems').textContent = outOfStockProducts.length;
    
    // Load stock alerts
    loadStockAlerts();
    
    // Load tables
    loadOrdersTable();
    loadAppointmentsTable();
    loadFeedbackTable();
    loadProductsTable();
}

function loadStockAlerts() {
    const alertsContainer = document.getElementById('stockAlerts');
    if (!alertsContainer) return;
    
    const lowStockProducts = getLowStockProducts();
    const outOfStockProducts = getOutOfStockProducts();
    
    let alertsHTML = '';
    
    if (outOfStockProducts.length > 0) {
        alertsHTML += '<div class="alert alert-danger">';
        alertsHTML += '<h4>🚨 Out of Stock Items</h4>';
        alertsHTML += '<ul>';
        outOfStockProducts.forEach(product => {
            alertsHTML += `<li>${product.name} - SKU: ${product.sku || 'N/A'}</li>`;
        });
        alertsHTML += '</ul></div>';
    }
    
    if (lowStockProducts.length > 0) {
        alertsHTML += '<div class="alert alert-warning">';
        alertsHTML += '<h4>⚠️ Low Stock Items</h4>';
        alertsHTML += '<ul>';
        lowStockProducts.forEach(product => {
            alertsHTML += `<li>${product.name} - ${product.stock} units left (Min: ${product.minStock})</li>`;
        });
        alertsHTML += '</ul></div>';
    }
    
    if (alertsHTML === '') {
        alertsHTML = '<div class="alert alert-success">✅ All products are well stocked!</div>';
    }
    
    alertsContainer.innerHTML = alertsHTML;
}

function loadOrdersTable() {
    const orders = JSON.parse(localStorage.getItem('orders')) || [];
    const tbody = document.getElementById('ordersTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = orders.map(order => `
        <tr>
            <td>${order.orderNumber}</td>
            <td>${order.items[0]?.name || 'N/A'}</td>
            <td>${new Date(order.date).toLocaleDateString()}</td>
            <td>${formatPrice(order.total)}</td>
            <td><span class="status-badge ${order.status}">${order.status}</span></td>
            <td>
                <button class="admin-action-btn admin-view-btn" onclick="viewOrderDetails('${order.orderNumber}')">View</button>
                <button class="admin-action-btn admin-edit-btn" onclick="updateOrderStatus('${order.orderNumber}')">Update</button>
            </td>
        </tr>
    `).join('');
}

function loadAppointmentsTable() {
    const appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    const tbody = document.getElementById('appointmentsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = appointments.map(apt => `
        <tr>
            <td>${apt.id}</td>
            <td>${apt.customerName}</td>
            <td>${apt.serviceType}</td>
            <td>${new Date(apt.appointmentDate).toLocaleDateString()}</td>
            <td>${apt.appointmentTime}</td>
            <td><span class="status-badge ${apt.status}">${apt.status}</span></td>
            <td>
                <button class="admin-action-btn admin-view-btn" onclick="viewAppointmentDetails('${apt.id}')">View</button>
                <button class="admin-action-btn admin-edit-btn" onclick="updateAppointmentStatus('${apt.id}')">Update</button>
            </td>
        </tr>
    `).join('');
}

function loadFeedbackTable() {
    const feedbacks = JSON.parse(localStorage.getItem('feedbacks')) || [];
    const tbody = document.getElementById('feedbackTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = feedbacks.map(fb => `
        <tr>
            <td>${fb.id}</td>
            <td>${fb.customerName}</td>
            <td>${fb.feedbackType}</td>
            <td>${'★'.repeat(fb.rating)}${'☆'.repeat(5-fb.rating)}</td>
            <td><span class="sentiment-badge ${fb.sentiment}">${fb.sentiment}</span></td>
            <td>${new Date(fb.createdAt).toLocaleDateString()}</td>
            <td>
                <button class="admin-action-btn admin-view-btn" onclick="viewFeedbackDetails('${fb.id}')">View</button>
            </td>
        </tr>
    `).join('');
}

function loadProductsTable() {
    const tbody = document.getElementById('productsTableBody');
    if (!tbody) return;
    
    tbody.innerHTML = products.map(product => `
        <tr class="${!product.isAvailable ? 'out-of-stock' : product.stock <= product.minStock ? 'low-stock' : ''}">
            <td>${product.id}</td>
            <td>
                <div class="product-info">
                    <div class="product-name">${product.name}</div>
                    <div class="product-category">${product.category}</div>
                    <div class="product-sku">SKU: ${product.sku || 'N/A'}</div>
                </div>
            </td>
            <td>${formatPrice(product.price)}</td>
            <td>
                <div class="stock-info">
                    <span class="stock-count ${product.stock === 0 ? 'out-of-stock' : product.stock <= product.minStock ? 'low-stock' : 'in-stock'}">
                        ${product.stock || 0} units
                    </span>
                    <div class="stock-actions">
                        <input type="number" id="stock-${product.id}" value="${product.stock || 0}" min="0" class="stock-input">
                        <button class="admin-action-btn small" onclick="updateProductStock(${product.id}, document.getElementById('stock-${product.id}').value)">Update</button>
                    </div>
                </div>
            </td>
            <td>
                <div class="availability-status">
                    <span class="status-badge ${product.isAvailable ? 'available' : 'unavailable'}">
                        ${product.isAvailable ? 'Available' : 'Sold Out'}
                    </span>
                    <button class="admin-action-btn small" onclick="toggleProductAvailability(${product.id})">
                        ${product.isAvailable ? 'Disable' : 'Enable'}
                    </button>
                </div>
            </td>
            <td>${product.rating.toFixed(1)}</td>
            <td>
                <div class="product-actions">
                    <button class="admin-action-btn admin-edit-btn" onclick="editProduct(${product.id})">Edit Price</button>
                    <button class="admin-action-btn admin-edit-btn" onclick="editProductDetails(${product.id})">Edit Details</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateOrderStatus(orderNumber) {
    const newStatus = prompt('Update order status (pending/shipped/delivered/cancelled):');
    if (newStatus && ['pending', 'shipped', 'delivered', 'cancelled'].includes(newStatus)) {
        const orders = JSON.parse(localStorage.getItem('orders')) || [];
        const orderIndex = orders.findIndex(o => o.orderNumber === orderNumber);
        if (orderIndex !== -1) {
            orders[orderIndex].status = newStatus;
            localStorage.setItem('orders', JSON.stringify(orders));
            loadOrdersTable();
            showNotification('Order status updated!');
        }
    }
}

function updateAppointmentStatus(appointmentId) {
    const newStatus = prompt('Update appointment status (pending/confirmed/completed/cancelled):');
    if (newStatus && ['pending', 'confirmed', 'completed', 'cancelled'].includes(newStatus)) {
        const appointments = JSON.parse(localStorage.getItem('appointments')) || [];
        const aptIndex = appointments.findIndex(a => a.id === appointmentId);
        if (aptIndex !== -1) {
            appointments[aptIndex].status = newStatus;
            localStorage.setItem('appointments', JSON.stringify(appointments));
            loadAppointmentsTable();
            loadAdminDashboard();
            showNotification('Appointment status updated!');
        }
    }
}

function viewAppointmentDetails(appointmentId) {
    const appointments = JSON.parse(localStorage.getItem('appointments')) || [];
    const appointment = appointments.find(a => a.id === appointmentId);
    
    if (!appointment) {
        showNotification('Appointment not found!');
                    return;
                }
                
    const details = `
Appointment ID: ${appointment.id}
Customer: ${appointment.customerName}
Email: ${appointment.customerEmail}
Phone: ${appointment.customerPhone}
Bike Model: ${appointment.bikeModel}
Service Type: ${appointment.serviceType}
Date: ${appointment.appointmentDate}
Time: ${appointment.appointmentTime}
Branch: ${appointment.branchLocation}
Problem: ${appointment.problemDescription}
Urgency: ${appointment.urgency}
Status: ${appointment.status}
    `;

    alert(details);
}

function viewFeedbackDetails(feedbackId) {
    const feedbacks = JSON.parse(localStorage.getItem('feedbacks')) || [];
    const feedback = feedbacks.find(f => f.id === feedbackId);
    
    if (!feedback) {
        showNotification('Feedback not found!');
                    return;
                }
                
    const details = `
Feedback ID: ${feedback.id}
Customer: ${feedback.customerName}
Email: ${feedback.customerEmail}
Type: ${feedback.feedbackType}
Rating: ${feedback.rating}/5
Recommendation: ${feedback.recommendation}
Message: ${feedback.feedbackMessage}
Sentiment: ${feedback.sentiment}
Date: ${new Date(feedback.createdAt).toLocaleString()}
    `;

    alert(details);
}

function editProduct(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;
    
    const newPrice = prompt(`Edit price for ${product.name} (current: ${formatPrice(product.price)}):`);
    if (newPrice && !isNaN(newPrice) && newPrice > 0) {
        product.price = parseFloat(newPrice);
        showNotification('Product price updated!');
        loadProductsTable();
    }
}

function editProductDetails(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const newName = prompt(`Enter new name for product:`, product.name);
    if (newName && newName.trim()) {
        product.name = newName.trim();
    }

    const newDescription = prompt(`Enter new description:`, product.description);
    if (newDescription && newDescription.trim()) {
        product.description = newDescription.trim();
    }

    const newMinStock = prompt(`Enter minimum stock level:`, product.minStock || 5);
    if (newMinStock && !isNaN(newMinStock) && newMinStock >= 0) {
        product.minStock = parseInt(newMinStock);
    }

    const newSku = prompt(`Enter SKU:`, product.sku || '');
    if (newSku && newSku.trim()) {
        product.sku = newSku.trim();
    }

    localStorage.setItem('products', JSON.stringify(products));
    loadProductsTable();
    showNotification('Product details updated successfully!');
}

// Fill credentials function for demo
function fillCredentials(username, password) {
    document.getElementById('username').value = username;
    document.getElementById('password').value = password;
}

function fillAdminCredentials(username, password) {
    document.getElementById('adminUsername').value = username;
    document.getElementById('adminPassword').value = password;
}

// Enhanced Product Management Functions
function updateProductStock(productId, newStock) {
    const product = products.find(p => p.id === productId);
    if (product) {
        product.stock = parseInt(newStock);
        product.isAvailable = product.stock > 0;
        
        // Check if stock is below minimum
        if (product.stock <= product.minStock) {
            showNotification(`⚠️ Low stock alert: ${product.name} has only ${product.stock} items left!`);
        }
        
        // Update localStorage
        localStorage.setItem('products', JSON.stringify(products));
        loadProductsTable();
        showNotification('Product stock updated successfully!');
    }
}

function toggleProductAvailability(productId) {
    const product = products.find(p => p.id === productId);
    if (product) {
        product.isAvailable = !product.isAvailable;
        localStorage.setItem('products', JSON.stringify(products));
        loadProductsTable();
        showNotification(`Product ${product.isAvailable ? 'enabled' : 'disabled'} successfully!`);
    }
}

function updateProductDetails(productId, field, value) {
    const product = products.find(p => p.id === productId);
    if (product) {
        product[field] = value;
        localStorage.setItem('products', JSON.stringify(products));
        loadProductsTable();
        showNotification('Product updated successfully!');
    }
}

function getLowStockProducts() {
    return products.filter(product => product.stock <= product.minStock);
}

function getOutOfStockProducts() {
    return products.filter(product => product.stock === 0);
}

document.getElementById('feedbackForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const feedback = document.getElementById('feedbackText').value.toLowerCase();
    const positiveWords = ['good', 'great', 'excellent', 'happy', 'love', 'satisfied', 'awesome', 'fantastic', 'amazing'];
    const negativeWords = ['bad', 'poor', 'terrible', 'sad', 'hate', 'unsatisfied', 'awful', 'disappoint', 'problem'];
    let score = 0;

    positiveWords.forEach(word => { if (feedback.includes(word)) score++; });
    negativeWords.forEach(word => { if (feedback.includes(word)) score--; });

    let sentiment = 'Neutral';
    if (score > 0) sentiment = 'Positive';
    else if (score < 0) sentiment = 'Negative';

    document.getElementById('sentimentResult').textContent = `Sentiment: ${sentiment}`;
});