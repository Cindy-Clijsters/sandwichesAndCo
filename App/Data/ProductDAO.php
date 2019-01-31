<?php
declare (strict_types = 1);

namespace App\Data;

use App\Entities\Product;
use App\Entities\ProductCategory;

use PDO;

class ProductDAO
{
    /**
     * Get an array with all the products
     * 
     * @return array
     */
    public function getAll():array
    {
        $result = [];
        
        // Generate the query
        $sql = "SELECT p.id, p.name, p.price, p.status, pc.id AS pc_id, pc.name AS pc_name, pc.status as pc_status
                FROM products p
                JOIN product_categories pc ON pc.id = p.product_category_id
                ORDER BY name";
        
        // Open the connection
        $pdo = DBConfig::getPdo();
        
        // Execute the query
        $resultSet = $pdo->query($sql);
        
        // Add the results to the array
        foreach ($resultSet as $row) {
            $product = $this->createFromDbRow($row);
            
            if ($product !== null) {
                array_push($result, $product);
            }
        }
        
        // Close the connection
        $pdo = null;
        
        // Return the result
        return $result;
    }
    
    /**
     * Create a product from a database row
     * 
     * @param array $row
     * 
     * @return Product|null
     */
    private function createFromDbRow(array $row):?Product 
    {   
        $product = null;
        
        if (
            array_key_exists('pc_id', $row)
            && array_key_exists('pc_name', $row) 
            && array_key_exists('pc_status', $row) 
        ) {
            
            $productCategory = ProductCategory::map(
                intVal($row['pc_id']),
                $row['pc_name'],
                $row['pc_status']
            );
            
            if (
                array_key_exists('id', $row)
                && array_key_exists('name', $row)
                && array_key_exists('price', $row)
                && array_key_exists('status', $row)
            ) {
                $product = Product::map(
                    intVal($row['id']),
                    $productCategory,
                    $row['name'],
                    $row['price'],
                    $row['status']
                );
            }
        }
        
        return $product;
    }
}