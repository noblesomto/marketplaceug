@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>{{ $page_title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">{{ $page_title }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0">{{ $page_title }}</h5>
                                <p class="text-muted mb-0">Manage user accounts and permissions</p>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshTable()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show" role="alert">
                                <i class="bi bi-{{ session('status')['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' }}-fill me-2"></i>
                                {{ session('status')['text'] }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Filter Section -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="accountTypeFilter">
                                    <option value="">All Account Types</option>
                                    <option value="Private">Private</option>
                                    <option value="Commercial">Commercial</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="verificationFilter">
                                    <option value="">All Verification Status</option>
                                    <option value="yes">Verified</option>
                                    <option value="no">Not Verified</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $users->total() }}</h4>
                                        <small>Total Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $users->where('verified', 'yes')->count() }}</h4>
                                        <small>Verified Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $users->where('verified', 'no')->count() }}</h4>
                                        <small>Unverified Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ $users->where('disable_account', 'yes')->count() }}</h4>
                                        <small>Disabled Users</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-person-fill me-1"></i>Name
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-shield-fill-check me-1"></i>Account Type
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-envelope-fill me-1"></i>Email
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-telephone-fill me-1"></i>Phone
                                        </th>
                                        <th scope="col" class="fw-semibold text-center">
                                            <i class="bi bi-patch-check-fill me-1"></i>Status
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-calendar-fill me-1"></i>Date Joined
                                        </th>
                                        <th scope="col" class="fw-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $row)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $row->name }}</h6>
                                                        <small class="text-muted">ID: {{ $row->user_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-capitalize">
                                                    {{ $row->acc_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="mailto:{{ $row->email }}" class="text-decoration-none">
                                                    {{ $row->email }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="tel:{{ $row->phone }}" class="text-decoration-none">
                                                    {{ $row->phone ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="badge {{ $row->verified === 'yes' ? 'bg-success' : 'bg-secondary' }}">
                                                        <i class="bi bi-{{ $row->verified === 'yes' ? 'check-circle' : 'x-circle' }} me-1"></i>
                                                        {{ ucfirst($row->verified) }}
                                                    </span>
                                                    @if($row->disable_account === 'yes')
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-slash-circle me-1"></i>Disabled
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ date('M j, Y', strtotime($row->created_at)) }}</span>
                                                <small class="d-block text-muted">{{ date('g:i A', strtotime($row->created_at)) }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/admin/view-user/{{ $row->user_id }}"
                                                       class="btn btn-outline-primary btn-sm"
                                                       title="View User Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    @if($row->disable_account === "no")
                                                        <button type="button"
                                                                class="btn btn-outline-warning btn-sm"
                                                                onclick="confirmAction('disable', '{{ $row->user_id }}', '{{ $row->name }}')"
                                                                title="Disable User">
                                                            <i class="bi bi-lock"></i>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-outline-success btn-sm"
                                                                onclick="confirmAction('enable', '{{ $row->user_id }}', '{{ $row->name }}')"
                                                                title="Enable User">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    @endif

                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="/admin/edit-user/{{ $row->user_id }}">
                                                                <i class="bi bi-pencil me-2"></i>Edit User
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="/admin/user-activity/{{ $row->user_id }}">
                                                                <i class="bi bi-activity me-2"></i>View Activity
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#"
                                                                   onclick="confirmDelete('{{ $row->user_id }}', '{{ $row->name }}')">
                                                                <i class="bi bi-trash me-2"></i>Delete User
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bi bi-people display-1 text-muted mb-3"></i>
                                                    <h5 class="text-muted">No users found</h5>
                                                    <p class="text-muted">There are no users matching your criteria.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($users->hasPages())
                            <div class="row align-items-center mt-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end">
                                        {{ $users->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-warning display-6"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0" id="confirmationMessage"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
// Enhanced confirmation function
function confirmAction(action, userId, userName) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmButton = document.getElementById('confirmButton');
    const message = document.getElementById('confirmationMessage');

    let actionText = action === 'disable' ? 'disable' : 'enable';
    let actionUrl = `/admin/disable-status/${userId}/${action === 'disable' ? 'Yes' : 'No'}`;

    message.textContent = `Are you sure you want to ${actionText} the account for "${userName}"?`;
    confirmButton.className = `btn btn-${action === 'disable' ? 'warning' : 'success'}`;
    confirmButton.textContent = action === 'disable' ? 'Disable Account' : 'Enable Account';

    confirmButton.onclick = function() {
        window.location.href = actionUrl;
    };

    modal.show();
}

function confirmDelete(userId, userName) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmButton = document.getElementById('confirmButton');
    const message = document.getElementById('confirmationMessage');

    message.textContent = `Are you sure you want to permanently delete the account for "${userName}"? This action cannot be undone.`;
    confirmButton.className = 'btn btn-danger';
    confirmButton.textContent = 'Delete Account';

    confirmButton.onclick = function() {
        // Add your delete logic here
        window.location.href = `/admin/delete-user/${userId}`;
    };

    modal.show();
}

// Table filtering and search functionality
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const accountType = document.getElementById('accountTypeFilter').value.toLowerCase();
    const verification = document.getElementById('verificationFilter').value.toLowerCase();
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

    for (let row of rows) {
        let showRow = true;
        const cells = row.getElementsByTagName('td');

        if (cells.length > 0) {
            const name = cells[0].textContent.toLowerCase();
            const email = cells[2].textContent.toLowerCase();
            const rowAccountType = cells[1].textContent.toLowerCase();
            const rowVerification = cells[4].textContent.toLowerCase();

            // Search filter
            if (searchInput && !name.includes(searchInput) && !email.includes(searchInput)) {
                showRow = false;
            }

            // Account type filter
            if (accountType && !rowAccountType.includes(accountType)) {
                showRow = false;
            }

            // Verification filter
            if (verification && !rowVerification.includes(verification)) {
                showRow = false;
            }
        }

        row.style.display = showRow ? '' : 'none';
    }
}

// Event listeners for filters
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('accountTypeFilter').addEventListener('change', filterTable);
document.getElementById('verificationFilter').addEventListener('change', filterTable);

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('accountTypeFilter').value = '';
    document.getElementById('verificationFilter').value = '';
    filterTable();
}

function refreshTable() {
    location.reload();
}

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

@include('backend.layouts.footer')
