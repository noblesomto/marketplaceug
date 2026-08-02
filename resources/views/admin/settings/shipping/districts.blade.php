@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
    <div class="d-flex justify-content-between align-items-center">
        <h1>District Shipping Fees</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">District Shipping Fees</li>
            </ol>
        </nav>
    </div>
</div><!-- End Page Title -->

<section class="section">
    <div class="row">
        <div class="col-lg-12">

            @if(session('status'))
                <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show">
                    {{ session('status')['text'] }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @foreach ($regions as $region)
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $region->name }}</h5>

                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">District</th>
                                    <th scope="col">Shipping Fee (UGX)</th>
                                    <th scope="col"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($region->lgas as $district)
                                <tr>
                                    <td class="align-middle">{{ $district->name }}</td>
                                    <form action="{{ route('admin.district-shipping.update', $district->id) }}" method="POST">
                                        @csrf
                                        <td>
                                            <input type="number" step="0.01" min="0" name="shipping_fee"
                                                   value="{{ old('shipping_fee', $district->shipping_fee) }}"
                                                   class="form-control" style="width: 160px;" required>
                                        </td>
                                        <td>
                                            <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                        </td>
                                    </form>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-muted">No districts found for this region.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            @endforeach

        </div>
    </div>
</section>

</main><!-- End #main -->

@include('admin.layouts.footer')
