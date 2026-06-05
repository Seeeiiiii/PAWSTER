<?php

include($_SERVER['DOCUMENT_ROOT'] . '/PAWSTER/config/app.php');

class prod_auto {
    public $products = [];

    public function __construct($db) {
        $this->fetchProducts($db);
    }
    
    public function fetchProducts($db) {
        $query = "SELECT * FROM tblsellerproduct";
        $result = mysqli_query($db->conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $this->products[] = $row;
        }
    }


    public function __clone() {
        $this->productimage = '';
        $this->brand_name = '';
        $this->product_desc = '';
        $this->primary_category = '';
    }

    public function __toString() {
        $output = "<p>Photo: " . $this->productimage . "<br>\n";
        $output .= "Brand: " . $this->brand_name . "<br>\n";
        $output .= "Description: " . $this->product_desc . "<br>\n";
        $output .= "Category: " . $this->primary_category . "<br>\n";
        return $output;
    }

    public function displayPhoto() {
        echo "<img src='" . $this->productimage . "' alt='Product Image'>";
    }

    public function displaySpecs() {
        echo "<p>Brand: " . $this->brand_name . "</p>";
        echo "<p>Description: " . $this->product_desc . "</p>";
        echo "<p>Category: " . $this->primary_category . "</p>";
    }
}
?>