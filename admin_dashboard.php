<?php
session_start();

if (isset($_GET['act'])) {
    switch ($_GET['act']) {
        case 'home':
            include "header.php";
            include "home.php";
            include "footer.php";
            break;
        case 'schedule':  
            include "header.php";  
            include "catalog/scheduleFilm.php";
            break;
        case 'ticket_price':
            include "header.php";
            include "catalog/TicketCost.php";
            break;
        case 'admin_promotions':
            include "header.php";
            include "catalog/admin_promotions.php";
            break;
        case 'about':
            include "catalog/about.php";
            break;
        case 'manage_movies':
            include "header.php";
            include "catalog/manage_movies.php";
            break;
        case 'manage_tickets':
            include "header.php";
            include "catalog/manage_tickets.php";
            break;
        case 'register':
            include "Register.php";
            break;
        case 'login':
            include "Log_in.php";
            break;
        case 'logout':
            header("Location: backend/log_out.php");
            exit();
            break;
        case 'search':
            include "catalog/search.php";
            break;
        default:
            include "header.php";
            include "home.php";
            include "footer.php";
            break;
    }
} else {
    include "header.php";
    include "home.php";
    include "footer.php";
}
?>