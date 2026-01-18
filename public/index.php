<?php
/**
 * Tire Fitment Application
 * Main entry point for the web application
 * WordPress-compatible via shortcode wrapper
 * 
 * Render-compatible: Works with PHP built-in server and Apache
 */

// For Render: Load environment variables if using .env file
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value);
        $_SERVER[trim($key)] = trim($value);
    }
}

// Handle health check before bootstrapping
if ($_SERVER['REQUEST_URI'] === '/healthz.php' || $_SERVER['REQUEST_URI'] === '/healthz') {
    require_once __DIR__ . '/healthz.php';
    exit;
}

require_once __DIR__ . '/../app/bootstrap.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tire Fitment Finder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/main.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Header -->
        <header class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Tire Fitment Finder</h1>
            <p class="text-gray-600">Enter your vehicle information to find compatible tires</p>
        </header>

        <!-- Disclaimer -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6" x-show="!loading">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        <strong>Important:</strong> Always verify tire size on your vehicle's driver door jamb or owner's manual before purchasing.
                    </p>
                </div>
            </div>
        </div>

        <!-- Main Application Container -->
        <div x-data="tireFitmentApp()" x-init="init()">
            <!-- Search Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6" x-show="!showResults">
                <!-- Tab Switcher -->
                <div class="flex border-b mb-6">
                    <button 
                        @click="searchMode = 'vin'" 
                        :class="searchMode === 'vin' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                        class="px-4 py-2 font-medium focus:outline-none"
                    >
                        Search by VIN
                    </button>
                    <button 
                        @click="searchMode = 'ymm'; resetForm()" 
                        :class="searchMode === 'ymm' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'"
                        class="px-4 py-2 font-medium focus:outline-none"
                    >
                        Search by Year/Make/Model
                    </button>
                </div>

                <!-- VIN Search Form -->
                <div x-show="searchMode === 'vin'" class="space-y-4">
                    <div>
                        <label for="vin" class="block text-sm font-medium text-gray-700 mb-2">
                            Vehicle Identification Number (VIN)
                        </label>
                        <input 
                            type="text" 
                            id="vin" 
                            x-model="vinInput"
                            @input="vinInput = vinInput.toUpperCase().replace(/[^A-HJ-NPR-Z0-9]/g, '').slice(0, 17)"
                            placeholder="Enter 17-character VIN"
                            maxlength="17"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :disabled="loading"
                        >
                        <p class="mt-1 text-sm text-gray-500" x-show="vinInput.length > 0">
                            <span x-text="vinInput.length"></span> / 17 characters
                        </p>
                    </div>

                    <button 
                        @click="searchByVIN()"
                        :disabled="vinInput.length !== 17 || loading"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        <span x-show="!loading">Search by VIN</span>
                        <span x-show="loading">Searching...</span>
                    </button>
                </div>

                <!-- YMM Search Form -->
                <div x-show="searchMode === 'ymm'" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Year -->
                        <div>
                            <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Year</label>
                            <select 
                                id="year" 
                                x-model="selectedYear"
                                @change="loadMakes()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :disabled="loading"
                            >
                                <option value="">Select Year</option>
                                <template x-for="year in years" :key="year">
                                    <option :value="year" x-text="year"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Make -->
                        <div>
                            <label for="make" class="block text-sm font-medium text-gray-700 mb-2">Make</label>
                            <select 
                                id="make" 
                                x-model="selectedMake"
                                @change="loadModels()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :disabled="!selectedYear || loading"
                            >
                                <option value="">Select Make</option>
                                <template x-for="make in makes" :key="make">
                                    <option :value="make" x-text="make"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Model -->
                        <div>
                            <label for="model" class="block text-sm font-medium text-gray-700 mb-2">Model</label>
                            <select 
                                id="model" 
                                x-model="selectedModel"
                                @change="loadTrims()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :disabled="!selectedMake || loading"
                            >
                                <option value="">Select Model</option>
                                <template x-for="model in models" :key="model">
                                    <option :value="model" x-text="model"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Trim -->
                        <div>
                            <label for="trim" class="block text-sm font-medium text-gray-700 mb-2">Trim (Optional)</label>
                            <select 
                                id="trim" 
                                x-model="selectedTrim"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :disabled="!selectedModel || loading"
                            >
                                <option value="">Any Trim</option>
                                <template x-for="trim in trims" :key="trim">
                                    <option :value="trim" x-text="trim"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    <button 
                        @click="searchByYMM()"
                        :disabled="!selectedYear || !selectedMake || !selectedModel || loading"
                        class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                    >
                        <span x-show="!loading">Find Tires</span>
                        <span x-show="loading">Searching...</span>
                    </button>
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage" class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                    <p class="text-sm text-red-700" x-text="errorMessage"></p>
                </div>
            </div>

            <!-- Results Section -->
            <div x-show="showResults" class="space-y-6">
                <!-- Vehicle Info Card -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                <span x-text="results.vehicle.year"></span>
                                <span x-text="results.vehicle.make"></span>
                                <span x-text="results.vehicle.model"></span>
                                <span x-show="results.vehicle.trim" x-text="' - ' + results.vehicle.trim"></span>
                            </h2>
                            <p class="text-gray-600 mt-1">Confirmed Vehicle</p>
                        </div>
                        <button 
                            @click="resetSearch()"
                            class="text-blue-600 hover:text-blue-800 font-medium"
                        >
                            New Search
                        </button>
                    </div>

                    <!-- Fitment Info -->
                    <div class="bg-gray-50 rounded-lg p-4 mb-4">
                        <h3 class="font-semibold text-gray-900 mb-2">Recommended Tire Sizes</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm font-medium text-gray-600">Front Tires:</span>
                                <span class="ml-2 text-lg font-semibold text-gray-900" x-text="results.fitment.front_tire"></span>
                            </div>
                            <div x-show="results.fitment.is_staggered">
                                <span class="text-sm font-medium text-gray-600">Rear Tires:</span>
                                <span class="ml-2 text-lg font-semibold text-gray-900" x-text="results.fitment.rear_tire"></span>
                            </div>
                        </div>
                        <p x-show="results.fitment.is_staggered" class="text-sm text-gray-600 mt-2">
                            <em>This vehicle uses a staggered tire setup (different front and rear sizes)</em>
                        </p>
                    </div>
                </div>

                <!-- Front Tires -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        Front Tires (<span x-text="results.fitment.front_tire"></span>)
                    </h3>
                    <div x-show="results.tires.front.length === 0" class="text-center py-8 text-gray-500">
                        No matching tires found in stock for this size.
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-show="results.tires.front.length > 0">
                        <template x-for="tire in results.tires.front" :key="tire.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <h4 class="font-semibold text-lg text-gray-900" x-text="tire.brand + ' ' + tire.model"></h4>
                                <div class="mt-2 space-y-1 text-sm text-gray-600">
                                    <p><span class="font-medium">Size:</span> <span x-text="tire.tire_size"></span></p>
                                    <p><span class="font-medium">Season:</span> <span x-text="tire.season"></span></p>
                                    <p x-show="tire.load_index"><span class="font-medium">Load Index:</span> <span x-text="tire.load_index"></span></p>
                                    <p x-show="tire.speed_rating"><span class="font-medium">Speed Rating:</span> <span x-text="tire.speed_rating"></span></p>
                                    <p><span class="font-medium">Stock:</span> <span x-text="tire.stock" :class="tire.stock > 10 ? 'text-green-600' : tire.stock > 0 ? 'text-yellow-600' : 'text-red-600'"></span></p>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-2xl font-bold text-blue-600">$<span x-text="parseFloat(tire.price).toFixed(2)"></span></span>
                                    <div class="space-x-2">
                                        <button 
                                            @click="requestQuote(tire)"
                                            class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
                                        >
                                            Quote
                                        </button>
                                        <button 
                                            @click="addToCart(tire, 'front')"
                                            :disabled="tire.stock === 0"
                                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                                        >
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Rear Tires (if staggered) -->
                <div x-show="results.fitment.is_staggered" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        Rear Tires (<span x-text="results.fitment.rear_tire"></span>)
                    </h3>
                    <div x-show="results.tires.rear.length === 0" class="text-center py-8 text-gray-500">
                        No matching tires found in stock for this size.
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" x-show="results.tires.rear.length > 0">
                        <template x-for="tire in results.tires.rear" :key="tire.id">
                            <div class="border border-gray-200 rounded-lg p-4 hover:shadow-lg transition-shadow">
                                <h4 class="font-semibold text-lg text-gray-900" x-text="tire.brand + ' ' + tire.model"></h4>
                                <div class="mt-2 space-y-1 text-sm text-gray-600">
                                    <p><span class="font-medium">Size:</span> <span x-text="tire.tire_size"></span></p>
                                    <p><span class="font-medium">Season:</span> <span x-text="tire.season"></span></p>
                                    <p x-show="tire.load_index"><span class="font-medium">Load Index:</span> <span x-text="tire.load_index"></span></p>
                                    <p x-show="tire.speed_rating"><span class="font-medium">Speed Rating:</span> <span x-text="tire.speed_rating"></span></p>
                                    <p><span class="font-medium">Stock:</span> <span x-text="tire.stock" :class="tire.stock > 10 ? 'text-green-600' : tire.stock > 0 ? 'text-yellow-600' : 'text-red-600'"></span></p>
                                </div>
                                <div class="mt-4 flex justify-between items-center">
                                    <span class="text-2xl font-bold text-blue-600">$<span x-text="parseFloat(tire.price).toFixed(2)"></span></span>
                                    <div class="space-x-2">
                                        <button 
                                            @click="requestQuote(tire)"
                                            class="px-3 py-1 text-sm bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition-colors"
                                        >
                                            Quote
                                        </button>
                                        <button 
                                            @click="addToCart(tire, 'rear')"
                                            :disabled="tire.stock === 0"
                                            class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors"
                                        >
                                            Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Loading Overlay -->
            <div x-show="loading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-lg p-6 text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                    <p class="mt-4 text-gray-700">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Load scripts at end for proper initialization -->
    <script src="/assets/js/app.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
