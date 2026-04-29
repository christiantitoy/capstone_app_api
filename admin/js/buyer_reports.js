// /admin/js/buyer_reports.js

let currentPage = 1;
let totalPages = 1;
let currentReportId = null;

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
                <td colspan="8">
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
        <tr>
            <td><strong>#${report.id}</strong></td>
            <td>#${report.delivery_id}</td>
            <td>Buyer #${report.buyer_id}</td>
            <td><span class="issue-type">${report.issue_type}</span></td>
            <td><span class="status-badge status-${report.status}">${report.status}</span></td>
            <td>${formatDate(report.created_at)}</td>
            <td>${formatDate(report.updated_at)}</td>
            <td>
                <div class="action-buttons">
                    <button class="btn-action btn-action-edit" onclick="openStatusModal(${report.id}, '${report.status}')" title="Change Status">
                        <i class="fas fa-edit"></i> Status
                    </button>
                    ${report.status === 'pending' ? `
                        <button class="btn-action btn-action-review" onclick="quickUpdate(${report.id}, 'reviewing')" title="Mark as Reviewing">
                            <i class="fas fa-search"></i> Review
                        </button>
                    ` : ''}
                    ${report.status === 'reviewing' ? `
                        <button class="btn-action btn-action-resolve" onclick="quickUpdate(${report.id}, 'resolved')" title="Mark as Resolved">
                            <i class="fas fa-check"></i> Resolve
                        </button>
                    ` : ''}
                    ${report.status !== 'closed' ? `
                        <button class="btn-action btn-action-close" onclick="quickUpdate(${report.id}, 'closed')" title="Close Report">
                            <i class="fas fa-times"></i> Close
                        </button>
                    ` : ''}
                </div>
            </td>
        </tr>
    `).join('');
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
document.addEventListener('DOMContentLoaded', function() {
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
});

// Quick status update
function quickUpdate(reportId, newStatus) {
    updateReportStatusRequest(reportId, newStatus);
}

// Open status modal
function openStatusModal(reportId, currentStatus) {
    currentReportId = reportId;
    document.getElementById('modalReportId').textContent = reportId;
    document.getElementById('newStatusSelect').value = currentStatus;
    document.getElementById('statusModal').style.display = 'flex';
}

// Close status modal
function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
    currentReportId = null;
}

// Update report status
function updateReportStatus() {
    const newStatus = document.getElementById('newStatusSelect').value;
    if (currentReportId) {
        updateReportStatusRequest(currentReportId, newStatus);
        closeStatusModal();
    }
}

// API call to update status
function updateReportStatusRequest(reportId, newStatus) {
    fetch('/admin/backend/reports/get_buyer_reports.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            report_id: reportId,
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            fetchReports(); // Refresh the list
            showToast('Status updated successfully', 'success');
        } else {
            showToast('Failed to update status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Network error occurred', 'error');
    });
}

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
            <td colspan="8">
                <div class="empty-state">
                    <i class="fas fa-exclamation-triangle" style="color: #e74c3c;"></i>
                    <p>${message}</p>
                    <small>Please try again later</small>
                </div>
            </td>
        </tr>
    `;
}

// Helper: Show toast notification
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    
    // Add styles inline
    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        padding: '12px 20px',
        borderRadius: '8px',
        color: 'white',
        display: 'flex',
        alignItems: 'center',
        gap: '10px',
        zIndex: '10000',
        animation: 'slideIn 0.3s ease',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)'
    });
    
    if (type === 'success') {
        toast.style.background = '#27ae60';
    } else if (type === 'error') {
        toast.style.background = '#e74c3c';
    } else {
        toast.style.background = '#3498db';
    }
    
    document.body.appendChild(toast);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const statusModal = document.getElementById('statusModal');
    if (event.target === statusModal) {
        closeStatusModal();
    }
};

// Add toast animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);