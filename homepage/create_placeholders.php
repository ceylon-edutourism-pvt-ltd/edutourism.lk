<?php
/**
 * Placeholder Image Generator for Past Tours
 * Run this script once to create placeholder images
 * Usage: php create_placeholders.php
 */

// Function to create a placeholder image
function createPlaceholder($width, $height, $text, $filename, $bgColor = null) {
    $image = imagecreatetruecolor($width, $height);
    
    // Random background colors for different tours
    if ($bgColor === null) {
        $bgColor = imagecolorallocate($image, rand(100, 200), rand(100, 200), rand(150, 255));
    }
    
    $textColor = imagecolorallocate($image, 255, 255, 255);
    $darkColor = imagecolorallocate($image, 50, 50, 50);
    
    // Fill background
    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    
    // Add some visual interest - gradient effect
    for ($i = 0; $i < $height; $i++) {
        $alpha = ($i / $height) * 50;
        $overlayColor = imagecolorallocatealpha($image, 0, 0, 0, $alpha);
        imageline($image, 0, $i, $width, $i, $overlayColor);
    }
    
    // Add text
    $fontSize = 5;
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    $textHeight = imagefontheight($fontSize);
    $x = ($width - $textWidth) / 2;
    $y = ($height - $textHeight) / 2;
    
    // Text shadow
    imagestring($image, $fontSize, $x + 2, $y + 2, $text, $darkColor);
    // Main text
    imagestring($image, $fontSize, $x, $y, $text, $textColor);
    
    // Save image
    imagejpeg($image, $filename, 85);
    imagedestroy($image);
    
    return file_exists($filename);
}

echo "Creating placeholder images for Past Tours module...\n\n";

// Define base path
$basePath = 'img/past_tours/';

// Ensure directories exist
$dirs = [
    $basePath,
    $basePath . 'japan_2025/',
    $basePath . 'singapore_2024/',
    $basePath . 'thailand_2024/'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
        echo "Created directory: $dir\n";
    }
}

// Color schemes for different tours
$japanColor = imagecolorallocate(imagecreatetruecolor(1, 1), 219, 112, 147); // Pink
$singaporeColor = imagecolorallocate(imagecreatetruecolor(1, 1), 100, 149, 237); // Blue
$thailandColor = imagecolorallocate(imagecreatetruecolor(1, 1), 255, 140, 0); // Orange

// Create cover images
echo "\nCreating cover images...\n";
createPlaceholder(800, 600, 'Japan 2025 - Cultural Tour', $basePath . 'japan_2025_cover.jpg');
createPlaceholder(800, 600, 'Singapore 2024 - Education', $basePath . 'singapore_2024_cover.jpg');
createPlaceholder(800, 600, 'Thailand 2024 - Historical', $basePath . 'thailand_2024_cover.jpg');
echo "✓ Cover images created\n";

// Create Japan tour images
echo "\nCreating Japan tour images...\n";
$japanImages = [
    'hero.jpg' => 'Fushimi Inari Shrine',
    'temple.jpg' => 'Golden Pavilion - Kyoto',
    'technology.jpg' => 'Tokyo Tech Center',
    'tea_ceremony.jpg' => 'Tea Ceremony Experience',
    'sakura.jpg' => 'Cherry Blossoms - Ueno Park',
    'shibuya.jpg' => 'Shibuya Crossing',
    'calligraphy.jpg' => 'Calligraphy Workshop'
];

foreach ($japanImages as $filename => $text) {
    createPlaceholder(1200, 800, $text, $basePath . 'japan_2025/' . $filename);
    echo "✓ Created: $filename\n";
}

// Create Singapore tour images
echo "\nCreating Singapore tour images...\n";
$singaporeImages = [
    'hero.jpg' => 'Marina Bay Sands',
    'nus.jpg' => 'National University of Singapore',
    'gardens.jpg' => 'Gardens by the Bay',
    'science.jpg' => 'Science Centre',
    'chinatown.jpg' => 'Chinatown Heritage',
    'sentosa.jpg' => 'Sentosa Island',
    'hawker.jpg' => 'Hawker Centre Food'
];

foreach ($singaporeImages as $filename => $text) {
    createPlaceholder(1200, 800, $text, $basePath . 'singapore_2024/' . $filename);
    echo "✓ Created: $filename\n";
}

// Create Thailand tour images
echo "\nCreating Thailand tour images...\n";
$thailandImages = [
    'hero.jpg' => 'Grand Palace Bangkok',
    'temple_dawn.jpg' => 'Wat Arun - Temple of Dawn',
    'cooking.jpg' => 'Thai Cooking Class',
    'elephant.jpg' => 'Elephant Sanctuary',
    'university.jpg' => 'Chulalongkorn University',
    'floating_market.jpg' => 'Floating Market',
    'cultural_show.jpg' => 'Traditional Dance'
];

foreach ($thailandImages as $filename => $text) {
    createPlaceholder(1200, 800, $text, $basePath . 'thailand_2024/' . $filename);
    echo "✓ Created: $filename\n";
}

echo "\n";
echo "========================================\n";
echo "✓ All placeholder images created!\n";
echo "========================================\n";
echo "\nTotal images created: " . (3 + count($japanImages) + count($singaporeImages) + count($thailandImages)) . "\n";
echo "\nYou can now:\n";
echo "1. Run the SQL schema in phpMyAdmin\n";
echo "2. Visit pasttours.php to see your tours\n";
echo "3. Replace placeholders with real photos later\n\n";
?>
