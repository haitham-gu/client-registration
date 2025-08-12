// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', () => {
    let allClients = [];
    let filteredClients = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    // Initialize dashboard
    loadData();
    setupEventListeners();

    function setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        let searchTimeout;
        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterData();
            }, 300);
        });

        // Filter by wilaya
        document.getElementById('filterWilaya').addEventListener('change', filterData);
    }

    async function loadData() {
        try {
            showLoading();
            const response = await fetch('../api/admin.php?action=list');
            
            if (response.status === 401) {
                // Unauthorized - redirect to login
                window.location.href = 'login.html';
                return;
            }
            
            if (!response.ok) throw new Error('Failed to load data');
            
            const data = await response.json();
            allClients = data.clients || [];
            
            updateStats(data.stats || {});
            populateWilayaFilter();
            filterData();
            hideLoading();
        } catch (error) {
            console.error('Error loading data:', error);
            showError('خطأ في تحميل البيانات');
        }
    }

    function updateStats(stats) {
        document.getElementById('totalClients').textContent = stats.total || 0;
        document.getElementById('todayClients').textContent = stats.today || 0;
        document.getElementById('thisWeekClients').textContent = stats.thisWeek || 0;
        document.getElementById('topWilaya').textContent = stats.topWilaya || '-';
    }

    function populateWilayaFilter() {
        const wilayaFilter = document.getElementById('filterWilaya');
        const wilayas = [...new Set(allClients.map(c => c.wilaya))].sort();
        
        // Clear existing options (except "All")
        wilayaFilter.innerHTML = '<option value="">جميع الولايات</option>';
        
        wilayas.forEach(wilaya => {
            const option = document.createElement('option');
            option.value = wilaya;
            option.textContent = wilaya;
            wilayaFilter.appendChild(option);
        });
    }

    function filterData() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
        const selectedWilaya = document.getElementById('filterWilaya').value;

        filteredClients = allClients.filter(client => {
            const matchesSearch = !searchTerm || 
                client.full_name.toLowerCase().includes(searchTerm) ||
                client.phone_dz.includes(searchTerm) ||
                client.phone_fr.includes(searchTerm) ||
                client.client_code.toLowerCase().includes(searchTerm);

            const matchesWilaya = !selectedWilaya || client.wilaya === selectedWilaya;

            return matchesSearch && matchesWilaya;
        });

        currentPage = 1;
        renderTable();
        renderPagination();
        updateResultsCount();
    }

    function renderTable() {
        const tbody = document.getElementById('clientsTableBody');
        const table = document.getElementById('clientsTable');
        const emptyState = document.getElementById('emptyState');

        if (filteredClients.length === 0) {
            table.style.display = 'none';
            emptyState.style.display = 'block';
            return;
        }

        table.style.display = 'table';
        emptyState.style.display = 'none';

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageClients = filteredClients.slice(startIndex, endIndex);

        tbody.innerHTML = pageClients.map(client => `
            <tr>
                <td><span class="client-code">${escapeHtml(client.client_code)}</span></td>
                <td>${escapeHtml(client.full_name)}</td>
                <td>${escapeHtml(client.phone_dz)}</td>
                <td>${escapeHtml(client.phone_fr)}</td>
                <td>${escapeHtml(client.wilaya)}</td>
                <td>${escapeHtml(client.city_fr)}</td>
                <td>${formatDate(client.created_at)}</td>
                <td>
                    <button class="btn btn-small btn-outline" onclick="copyClientCode('${client.client_code}')">نسخ الكود</button>
                    <button class="btn btn-small btn-outline" onclick="showClientDetails(${client.id})">التفاصيل</button>
                </td>
            </tr>
        `).join('');
    }

    function renderPagination() {
        const pagination = document.getElementById('pagination');
        const totalPages = Math.ceil(filteredClients.length / itemsPerPage);

        if (totalPages <= 1) {
            pagination.style.display = 'none';
            return;
        }

        pagination.style.display = 'flex';

        let paginationHTML = '';

        // Previous button
        paginationHTML += `
            <button ${currentPage === 1 ? 'disabled' : ''} onclick="changePage(${currentPage - 1})">
                السابق
            </button>
        `;

        // Page numbers
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                paginationHTML += `
                    <button class="${i === currentPage ? 'active' : ''}" onclick="changePage(${i})">
                        ${i}
                    </button>
                `;
            } else if (i === currentPage - 3 || i === currentPage + 3) {
                paginationHTML += '<span>...</span>';
            }
        }

        // Next button
        paginationHTML += `
            <button ${currentPage === totalPages ? 'disabled' : ''} onclick="changePage(${currentPage + 1})">
                التالي
            </button>
        `;

        pagination.innerHTML = paginationHTML;
    }

    function updateResultsCount() {
        const resultsCount = document.getElementById('resultsCount');
        resultsCount.textContent = `${filteredClients.length} من ${allClients.length} عميل`;
    }

    function showLoading() {
        document.getElementById('loadingState').style.display = 'block';
        document.getElementById('clientsTable').style.display = 'none';
        document.getElementById('emptyState').style.display = 'none';
    }

    function hideLoading() {
        document.getElementById('loadingState').style.display = 'none';
    }

    function showError(message) {
        hideLoading();
        alert(message); // In production, use a nicer toast notification
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ar-DZ', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Global functions for button handlers
    window.refreshData = loadData;

    window.changePage = (page) => {
        currentPage = page;
        renderTable();
        renderPagination();
    };

    window.copyClientCode = async (code) => {
        try {
            await navigator.clipboard.writeText(code);
            // In production, show a toast notification
            alert('تم نسخ الكود: ' + code);
        } catch (error) {
            console.error('Copy failed:', error);
        }
    };

    window.showClientDetails = (clientId) => {
        const client = allClients.find(c => c.id == clientId);
        if (!client) return;

        const details = `
الاسم: ${client.full_name}
كود العميل: ${client.client_code}
هاتف الجزائر: ${client.phone_dz}
هاتف فرنسا: ${client.phone_fr}
الولاية: ${client.wilaya}
المدينة الفرنسية: ${client.city_fr}
تاريخ التسجيل: ${formatDate(client.created_at)}
        `.trim();

        alert(details); // In production, use a modal
    };

    window.exportData = async () => {
        try {
            const response = await fetch('../api/admin.php?action=export');
            if (!response.ok) throw new Error('Export failed');

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `clients_${new Date().toISOString().split('T')[0]}.csv`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Export error:', error);
            alert('خطأ في تصدير البيانات');
        }
    };

    window.logout = async () => {
        if (confirm('هل تريد تسجيل الخروج؟')) {
            try {
                await fetch('../api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                });
            } catch (error) {
                console.error('Logout error:', error);
            }
            window.location.href = 'login.html';
        }
    };
});
