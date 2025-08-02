@include('backend.layouts.header')
@include('backend.layouts.nav')

<main id="main" class="main">

    <div class="pagetitle">
      <h1>Dashboard</h1>
      <nav>
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
          <li class="breadcrumb-item active">Dashboard</li>
        </ol>
      </nav>
    </div><!-- End Page Title -->

    <section class="section dashboard">
      <div class="row">

        <!-- Left side columns -->
        <div class="col-lg-12">
          <div class="row">

            <!-- Sales Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card sales-card">
                <div class="card-body">
                  <h5 class="card-title">Total Users</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-people"></i>
                    </div>
                    <div class="ps-3">
                      <h6>{{ $count_users }}</h6>
                      

                    </div>
                  </div>
                </div>

              </div>
            </div><!-- End Sales Card -->

            <!-- Advert Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Total Adverts</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-badge-ad"></i>
                    </div>
                    <div class="ps-3">
                      <h6>{{ $count_adverts }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Advert Card -->

            <!-- Advert Boost Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Active Boost Ads</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-rocket-takeoff"></i>
                    </div>
                    <div class="ps-3">
                      <h6>{{ $count_boost }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Advert Boost Card -->

            <!-- Pending Shipping Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Pending Shipping</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                        <i class="bi bi-truck-flatbed"></i>
                    </div>
                    <div class="ps-3">
                      <h6>{{ $count_pending_shipping }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Pending Shipping Card -->

            <!-- Pending Delivery Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Pending Delivery</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-truck"></i>
                    </div>
                    <div class="ps-3">
                      <h6>{{ $count_pending_confirmed_shipping }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Pending Delivery Card -->

            <!-- Pending Settlements Card -->
            <div class="col-xxl-4 col-md-6">
              <div class="card info-card revenue-card">
                <div class="card-body">
                  <h5 class="card-title">Pending Settlements</h5>
                  <div class="d-flex align-items-center">
                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                      <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="ps-3">
                      <h6>{{ $count_pending_settlements }}</h6>
                    </div>
                  </div>
                </div>
              </div>
            </div><!-- End Pending Settlements Card -->
           
              </div>
            </div><!-- End Top Selling -->

          </div>
        </div><!-- End Left side columns -->

   

      </div>
    </section>

  </main><!-- End #main -->
  @include('backend.layouts.footer')
