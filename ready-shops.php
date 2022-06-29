<!DOCTYPE html>
<!--
Template Name: Tinker - HTML Admin Dashboard Template
Author: Left4code
Website: http://www.left4code.com/
Contact: muhammadrizki@left4code.com
Purchase: https://themeforest.net/user/left4code/portfolio
Renew Support: https://themeforest.net/user/left4code/portfolio
License: You must have a valid license purchased only from themeforest(the above link) in order to legally use the theme for your project.
-->
<html lang="en" class="light">
    <!-- BEGIN: Head -->
    <?php include 'includes/head.php'; ?>
    
    <!-- END: Head -->
    <body class="main">
        <div class="flex overflow-hidden">
            <!-- BEGIN: Side Menu -->
            <?php include 'includes/sidebar.php'; ?>
            <!-- END: Side Menu -->
            <!-- BEGIN: Content -->
            <div class="content ">
                <!-- BEGIN: Top Bar -->
                <div class="top-bar -mx-4 px-4 md:mx-0 md:px-0">
                    <!-- BEGIN: Breadcrumb -->
                    <div class="-intro-x breadcrumb mr-auto hidden sm:flex"> <a href="">Mais envios</a> <i data-feather="chevron-right" class="breadcrumb__icon"></i> <a href="" class="breadcrumb--active">Lista de lojas</a> </div>
                    <!-- END: Breadcrumb -->
                </div>
                <!-- END: Top Bar -->
                <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
                    <h2 class="text-lg font-medium mr-auto">
                      Mais envios
                    </h2>
                    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
                        <a href="/new-shop.php" class="btn btn-primary shadow-md mr-2">Nova loja</a>
                    </div>
                </div>
                <!-- BEGIN: HTML Table Data -->
                <div class="grid grid-cols-12 gap-6 mt-5">
                    <div class="intro-y col-span-12 lg:col-span-12">
                        <div class="intro-y box">
                            <div class="flex flex-col sm:flex-row items-center p-5 border-b border-gray-200 dark:border-dark-5">
                                <h2 class="font-medium text-base mr-auto">
                                    Lista de lojas
                                </h2>
                            </div>
                            <div class="p-5">
                                <table class="table">
                                <thead>
                                    <tr>
                                        <th class="border-b-2 dark:border-dark-5 whitespace-nowrap">#</th>
                                        <th class="border-b-2 dark:border-dark-5 whitespace-nowrap">Nome</th>
                                        <th class="border-b-2 dark:border-dark-5 whitespace-nowrap">Account</th>
                                        <th class="border-b-2 dark:border-dark-5 whitespace-nowrap">Plataforma</th>
                                        <th class="border-b-2 dark:border-dark-5 whitespace-nowrap">Ativo</th>
                                        <th class="border-b-2 dark:border-dark-5 whitespace-nowrap">Ações</th>
                                    </tr>
                                </thead>
                                <tbody id="ready-shops">
                                    
                                </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: HTML Table Data -->
            </div>
            <!-- END: Content -->
        </div>
        <!-- BEGIN: JS Assets-->
        <?php include 'includes/scripts.php'; ?>
        <script src="dist/routes/ready-shops.js?<?php echo rand(100,999).'='.rand(100,999);?>"></script>
        <!-- END: JS Assets-->
    </body>
</html>