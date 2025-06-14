<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="copyright" content="MACode ID, https://macodeid.com/">

  <title>JNEC E-Leave Portal</title>

  <link rel="stylesheet" href="../assets/css/maicons.css">
  <link rel="stylesheet" href="../assets/css/bootstrap.css">
  <link rel="stylesheet" href="../assets/vendor/owl-carousel/css/owl.carousel.css">
  <link rel="stylesheet" href="../assets/vendor/animate/animate.css">
  <link rel="stylesheet" href="../assets/css/theme.css">

  <!-- Custom Styles Added Here -->
  <style>
    /* Apply Times New Roman globally */
    body {
      font-family: "Times New Roman", Times, serif;
    }

    /* Style the main hero heading */
    .hero-section .subhead {
      font-size: 3rem; /* Increased font size (adjust as needed) */
      font-weight: bold;   /* Make it bold */
      font-style: normal; /* Ensure it's not italic if it was */
      display: block; /* Ensure it takes block-level properties */
      margin-bottom: 0.5rem; /* Add some space below */
       /* Keep template color or uncomment below if needed */
      /* color: white; */
    }

    /* Style the hero subheading (which is the h1 tag in this template) */
    .hero-section h1.display-4 {
      font-size: 1.5rem; /* Reduced font size (adjust as needed) */
      font-style: italic;  /* Make it italic */
      font-weight: normal; /* Make it normal weight (override display-4 default) */
       /* Adjust color if needed, maybe slightly dimmer */
      /* color: #f0f0f0; */
    }

    /* Ensure other text using specific fonts override the body rule if necessary */
    /* Example: If the brand needed a different font */
    /* .navbar-brand { font-family: 'Your Brand Font', sans-serif; } */
  </style>
  <!-- End Custom Styles -->

</head>
<body>

  <!-- Back to top button -->
  <div class="back-to-top"></div>

  <header>
    <div class="topbar">
      <div class="container">
        <div class="row">
          <div class="col-sm-8 text-sm">
            <div class="site-info">
              <a href="#"><span class="mai-call text-primary"></span> 77712842/17742370</a>

            </div>
          </div>
          <div class="col-sm-4 text-right text-sm">
            <div class="social-mini-button">
              <a href="https://www.facebook.com/YourPageName" target="_blank"><span class="mai-logo-facebook-f"></span></a>
              <a href="https://wa.me/YourNumber" target="_blank"><span class="mai-logo-whatsapp"></span></a>
            </div>
          </div>
        </div> <!-- .row -->
      </div> <!-- .container -->
    </div> <!-- .topbar -->

    <nav class="navbar navbar-expand-lg navbar-light shadow-sm">
      <div class="container">
        <a class="navbar-brand" href="#"><span class="text-primary">JNEC</span> E-Leave</a>

        <!-- Removed the search form -->
        <!--
        <form action="#">
          <div class="input-group input-navbar">
            <div class="input-group-prepend">
              <span class="input-group-text" id="icon-addon1"><span class="mai-search"></span></span>
            </div>
            <input type="text" class="form-control" placeholder="Enter keyword.." aria-label="Username" aria-describedby="icon-addon1">
          </div>
        </form>
        -->

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupport" aria-controls="navbarSupport" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupport">
          <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
              <a class="nav-link" href="/">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="/html/about.html">About Us</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#mission-vision">Our Mission and Vision</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#services">Services</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#system-overview">System Overview</a>
            </li>

            {{-- ======================================== --}}
            {{-- CORRECTED AUTHENTICATION LINKS START     --}}
            {{-- ======================================== --}}
            @if (Route::has('login'))
                @auth
                    {{-- Show these links if the user IS logged in --}}
                    <li class="nav-item">
                        {{-- Link to the user's dashboard (UPDATE href IF NEEDED) --}}
                        <a href="{{ url('/dashboard') }}" class="nav-link">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        {{-- Logout Form --}}
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <a href="{{ route('logout') }}"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();"
                                    class="btn btn-outline-primary ml-lg-3"> {{-- Or use nav-link class for different style --}}
                                Logout
                            </a>
                        </form>
                    </li>
                @else
                    {{-- Show this link if the user is NOT logged in --}}
                    <li class="nav-item">
                        <a class="btn btn-primary ml-lg-3" href="{{ route('login') }}">Login</a>
                    </li>

                    {{-- Register button removed from here --}}

                @endauth
            @endif
            {{-- ======================================== --}}
            {{-- CORRECTED AUTHENTICATION LINKS END       --}}
            {{-- ======================================== --}}

          </ul>
        </div> <!-- .navbar-collapse -->
      </div> <!-- .container -->
    </nav>
  </header>

  <!-- Hero Section with Updated Text and Styling -->
  <div class="page-hero bg-image overlay-dark" style="background-image: url(/assets/img/DJI_0008-scaled.jpg);">
    <div class="hero-section">
      <div class="container text-center wow zoomIn">
        <!-- This span is now styled to be big and bold -->
        <span class="subhead">Welcome to JNEC E-Leave Portal</span>
        <!-- This h1 is now styled to be small and italic -->
        <h1 class="display-4">Your convenient solution for managing leave requests.</h1>
        <a href="/html/started.html" class="btn btn-primary">Get Started</a>
      </div>
    </div>
  </div>
  <!-- End of Hero Section -->


  <div class="bg-light">
     <!-- Content previously removed/commented out remains the same -->
     <!-- ... -->

    <!-- Updating the Welcome Section -->
    <div class="page-section pb-0" id="about"> <!-- Added an ID for potential navigation -->
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-6 py-3 wow fadeInUp">
            <h1>Streamline Your Leave Management</h1>
            <p class="text-grey mb-4">The JNEC E-Leave Portal provides an efficient and transparent way for employees and management to handle leave requests. Submit applications, track approvals, and view leave balances all in one place. Reduce paperwork and save time with our easy-to-use online system.</p>
            <a href="/html/about.html" class="btn btn-primary">Learn More</a>
          </div>
          <div class="col-lg-6 wow fadeInRight" data-wow-delay="400ms">
            <div class="img-place custom-img-1">
              <img src="/assets/img/image.png" alt=""> <!-- Consider changing image -->
            </div>
          </div>
        </div>
      </div>
    </div> <!-- .bg-light -->
  </div> <!-- .bg-light -->

   <!-- Placeholder Sections remain the same -->
   <div id="mission-vision" class="page-section">
      <!-- ... content ... -->
      <div class="container text-center">
        <h2 class="mb-4"><b>Our Mission and Vision</b></h2>
        <p><div class="section">
          <!-- Section Title (was H2, now H3) -->
          <h3>Vision</h3>
          <p>Our vision is to transform the student leave application process into an efficient, paperless, and automated system, ensuring a seamless user experience for both students and administrators.</p>
      </div>

      <!-- Mission Section -->
      <div class="section">
           <!-- Section Title (was H2, now H3) -->
          <h3>Mission</h3>
          <p>Our mission is to provide a secure, transparent, and easily accessible platform for students to apply for leave, track their attendance, and receive real-time updates. We aim to enhance administrative efficiency and improve the overall student experience.</p>
      </div></p>
      </div>
   </div>
   <div id="services" class="page-section bg-light">
      <!-- ... content ... -->
       <div class="container text-center">
        <h2 class="mb-4"><b>Services Offered</b></h2>
            <br><strong>Digital Leave Application:</strong> Students can easily submit leave requests online, specifying leave type, dates, and reasons.

             <br><strong>Leave Status Tracking:</strong> Students can view the real-time status of their applications, including approvals and rejections.
             <br><strong>Automated Notifications:</strong>  Instant alerts keep users informed about leave statuses and leave history.
              <br><strong>Leave History Records:</strong> Maintains detailed logs of all past leave applications for students and staff reference.
      </div></p>
      </div>
   </div>
   <div id="system-overview" class="page-section">
      <!-- ... content ... -->
       <div class="container text-center">
        <h2 class="mb-4"><b>System Overview</b></h2>
        <p> <h3>Why Choose Us?</h3>
          <br><strong>Efficient Leave Management:</strong> Apply, track, and manage leave applications with ease.
             <br><strong>Admin Privileges:</strong>Our website empowers administrators to manage users,departments and maintain students leave records to keep you on track.
             <br><strong>Seamless Integration:</strong> Our platform integrates with existing systems to make leave approvals, tracking, and notifications easier.
              <br><strong>User-Centered Design:</strong> We’ve designed the portal with students, faculty, and administrators in mind, ensuring an intuitive user experience.

      </div></p>
      </div>
   </div>

  <footer class="page-footer">
     <!-- Footer content remains the same -->
     <!-- ... -->
    <div class="container">
        <!-- ... -->
        <hr>
        <p id="copyright">Copyright © 2025 <a href="https://www.jnec.edu.bt/" target="_blank">JNEC</a>. All right reserved</p>
    </div>
  </footer>

<script src="../assets/js/jquery-3.5.1.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/vendor/owl-carousel/js/owl.carousel.min.js"></script>
<script src="../assets/vendor/wow/wow.min.js"></script>
<script src="../assets/js/theme.js"></script>

</body>
</html>