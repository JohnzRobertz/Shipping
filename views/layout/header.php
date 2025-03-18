<!DOCTYPE html>
<html lang="<?php echo $_SESSION['lang'] ?? 'th'; ?>">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title><?php echo __('site_title'); ?></title>
   <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <!-- Bootstrap Icons -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
   <!-- Google Fonts - Thai -->
   <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;700&family=Prompt:wght@300;400;500;700&display=swap" rel="stylesheet">
   <!-- Custom CSS -->
   <link href="assets/css/style.css" rel="stylesheet">
   <!-- Font Awesome -->
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body>
<nav class="navbar navbar-expand-lg fixed-top bg-white border-bottom">
   <div class="container-fluid px-4">
       <!-- Brand -->
       <a class="navbar-brand" href="index.php">
           <i class="bi bi-box-seam text-primary"></i>
           <span class="ms-2"><?php echo __('site_title'); ?></span>
       </a>

       <!-- Mobile Toggle -->
       <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
           <i class="bi bi-list"></i>
       </button>

       <!-- Navbar Content -->
       <div class="collapse navbar-collapse" id="navbarContent">
           <!-- Main Navigation -->
           <ul class="navbar-nav me-auto">
               <li class="nav-item">
                   <a class="nav-link <?php echo $page === 'home' ? 'active' : ''; ?>" href="index.php">
                       <i class="bi bi-house-door"></i>
                       <span class="ms-2"><?php echo __('home'); ?></span>
                   </a>
               </li>
               <?php if (isLoggedIn()): ?>
                   <li class="nav-item">
                       <a class="nav-link <?php echo $page === 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
                           <i class="bi bi-speedometer2"></i>
                           <span class="ms-2"><?php echo __('dashboard'); ?></span>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link <?php echo $page === 'shipments' ? 'active' : ''; ?>" href="index.php?page=shipments">
                           <i class="bi bi-box"></i>
                           <span class="ms-2"><?php echo __('shipments'); ?></span>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link <?php echo $page === 'lots' ? 'active' : ''; ?>" href="index.php?page=lots">
                           <i class="bi bi-boxes"></i>
                           <span class="ms-2"><?php echo __('lots'); ?></span>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link <?php echo $page === 'invoice' ? 'active' : ''; ?>" href="index.php?page=invoice">
                           <i class="bi bi-receipt"></i>
                           <span class="ms-2"><?php echo __('invoice'); ?></span>
                       </a>
                   </li>
                   <li class="nav-item">
                       <a class="nav-link <?php echo $page === 'customer' ? 'active' : ''; ?>" href="index.php?page=customer">
                           <i class="bi bi-people"></i>
                           <span class="ms-2"><?php echo __('customer'); ?></span>
                       </a>
                   </li>
               <?php endif; ?>
               <li class="nav-item">
                   <a class="nav-link <?php echo $page === 'tracking' ? 'active' : ''; ?>" href="index.php?page=tracking">
                       <i class="bi bi-search"></i>
                       <span class="ms-2"><?php echo __('track_shipment'); ?></span>
                   </a>
               </li>
           </ul>

           <!-- Right Side Navigation -->
           <ul class="navbar-nav ms-auto">
               <!-- Language Selector -->
               <li class="nav-item dropdown">
                   <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                       <i class="bi bi-translate"></i>
                       <span class="ms-2"><?php echo __('language'); ?></span>
                   </a>
                   <ul class="dropdown-menu dropdown-menu-end">
                       <li>
                           <a class="dropdown-item" href="index.php?lang=en">
                               <i class="bi bi-globe2 me-2"></i> English
                           </a>
                       </li>
                       <li>
                           <a class="dropdown-item" href="index.php?lang=th">
                               <i class="bi bi-globe2 me-2"></i> ไทย
                           </a>
                       </li>
                   </ul>
               </li>

               <!-- User Menu -->
               <?php if (isLoggedIn()): ?>
                   <li class="nav-item dropdown ms-3">
                       <a class="nav-link dropdown-toggle user-menu" href="#" data-bs-toggle="dropdown">
                           <div class="d-inline-block user-avatar">
                               <i class="bi bi-person-circle"></i>
                           </div>
                           <span class="ms-2"><?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span>
                       </a>
                       <ul class="dropdown-menu dropdown-menu-end">
                           <li>
                               <a class="dropdown-item" href="index.php?page=profile">
                                   <i class="bi bi-person me-2"></i>
                                   <?php echo __('profile'); ?>
                               </a>
                           </li>
                           <li><hr class="dropdown-divider"></li>
                           <li>
                               <a class="dropdown-item text-danger" href="index.php?page=auth&action=logout">
                                   <i class="bi bi-box-arrow-right me-2"></i>
                                   <?php echo __('logout'); ?>
                               </a>
                           </li>
                       </ul>
                   </li>
               <?php else: ?>
                   <li class="nav-item ms-3">
                       <a class="nav-link" href="index.php?page=auth&action=login">
                           <i class="bi bi-box-arrow-in-right"></i>
                           <span class="ms-2"><?php echo __('login'); ?></span>
                       </a>
                   </li>
               <?php endif; ?>
           </ul>
       </div>
   </div>
</nav>

<!-- เพิ่มส่วนนี้ในไฟล์ header.php ในตำแหน่งที่เหมาะสม -->
<div class="container mt-3">
    <?php include 'views/layout/alerts.php'; ?>
</div>

<main class="main-content px-4">

