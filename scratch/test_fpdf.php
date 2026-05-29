<?php
require_once __DIR__ . '/../vendor/autoload.php';
if (class_exists('Fpdf\\Fpdf')) {
    echo "Fpdf\\Fpdf exists!";
} else {
    echo "Fpdf\\Fpdf not found!";
}
