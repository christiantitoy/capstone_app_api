// /admin/js/buyer_reports.js

let currentPage = 1;
let totalPages = 1;

// Display current date
document.addEventListener('DOMContentLoaded', function() {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('currentDate').textContent = new Date().toLocaleDateString(undefined, options);
    
    // Load initial data
    fetchReports();
    
    // Filter handlers
    document.getElementById('statusFilter').addEventListener('change', function() {
        currentPage = 1;
        fetchReports();
    });
    
    document.getElementById('searchInput').addEventListener('keyup', debounce(function() {
        currentPage = 1;
        fetchReports();
    }, 300));
});

// Fetch reports from API
function fetchReports() {
    const status = document.getElementById('statusFilter').value;
    const search = document.getElementById('searchInput').value;
    
    let url = `/admin/backend/reports/get_buyer_reports.php?page=${currentPage}`;
    if (status) url += `&status=${status}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;
    
    fetch(url)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const data = result.data;
                renderReportsTable(data.reports);
                updatePagination(data.pagination);
                updateStats(data.reports);
            } else {
                showError('Failed to load reports');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Network error occurred');
        });
}

// Render reports table
function renderReportsTable(reports) {
    const tbody = document.getElementById('reportsTableBody');
    
    if (reports.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6">
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No reports found</p>
                        <small>Try adjusting your filters or check back later</small>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = reports.map(report => `
        <tr class="clickable-row" onclick="navigateToReport(${report.id})" title="Click to view report details">
            <td><strong>#${report.id}</strong></td>
            <td>#${report.delivery_id}</td>
            <td>Buyer #${report.buyer_id}</td>
            <td><span class="issue-type">${report.issue_type}</span></td>
            <td><span class="status-badge status-${report.status}">${report.status}</span></td>
            <td class="td-date">${formatDate(report.created_at)}</td>
        </tr>
    `).join('');
}

// Navigate to report details page
function navigateToReport(reportId) {
    window.location.href = `/admin/ui/report_details.php?id=${reportId}`;
}

// Update stats cards
function updateStats(reports) {
    const stats = {
        pending: 0,
        reviewing: 0,
        resolved: 0,
        closed: 0
    };
    
    reports.forEach(report => {
        if (stats.hasOwnProperty(report.status)) {
            stats[report.status]++;
        }
    });
    
    document.getElementById('pendingCount').textContent = stats.pending;
    document.getElementById('reviewingCount').textContent = stats.reviewing;
    document.getElementById('resolvedCount').textContent = stats.resolved;
    document.getElementById('closedCount').textContent = stats.closed;
}

// Update pagination
function updatePagination(pagination) {
    currentPage = pagination.current_page;
    totalPages = pagination.total_pages;
    
    document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages} (${pagination.total_reports} reports)`;
    document.getElementById('prevPage').disabled = currentPage <= 1;
    document.getElementById('nextPage').disabled = currentPage >= totalPages;
}

// Pagination button handlers
document.getElementById('prevPage').addEventListener('click', function() {
    if (currentPage > 1) {
        currentPage--;
        fetchReports();
    }
});

document.getElementById('nextPage').addEventListener('click', function() {
    if (currentPage < totalPages) {
        currentPage++;
        fetchReports();
    }
});

// Helper: Format date
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
    return date.toLocaleDateString(undefined, options);
}

// Helper: Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Helper: Show error
function showError(message) {
    document.getElementById('reportsTableBody').innerHTML = `
        <tr>
            <td colspan="6">
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                    <p>${message}</p>
                    <small>Please try again later</small>
                </div>
            </td>
        </tr>
    `;
}