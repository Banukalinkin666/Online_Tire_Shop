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

        <!-- Legal Disclaimer (MANDATORY - Prominent) -->
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-lg shadow-md" x-show="!loading">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-red-800 mb-1">⚠️ Legal Disclaimer</h3>
                    <p class="text-sm text-red-700 font-medium">
                        <strong>Vehicle and tire information is provided for reference only.</strong><br>
                        Always verify tire size using the vehicle door placard or owner's manual before purchasing.
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
                        @click="searchMode = 'ai'; resetForm()" 
                        :class="searchMode === 'ai' ? 'border-b-2 border-purple-600 text-purple-600' : 'text-gray-600'"
                        class="px-4 py-2 font-medium focus:outline-none"
                    >
                        🤖 AI Natural Language Search
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

                <!-- AI Natural Language Search Form -->
                <div x-show="searchMode === 'ai'" class="space-y-4">
                    <div class="bg-purple-50 border-l-4 border-purple-500 p-4 mb-4 rounded">
                        <p class="text-sm text-purple-800">
                            <strong>🤖 AI Natural Language Search:</strong> Ask about tire sizes in plain English! 
                            Example: "What tire sizes fit my 2018 honda civic with 16 wheels"
                        </p>
                    </div>
                    
                    <div>
                        <label for="naturalLanguageQuery" class="block text-sm font-medium text-gray-700 mb-2">
                            Ask about tire sizes <span class="text-red-500">*</span>
                        </label>
                        <textarea 
                            id="naturalLanguageQuery" 
                            x-model="naturalLanguageQuery"
                            rows="3"
                            placeholder="e.g., What tire sizes fit my 2018 honda civic with 16 wheels"
                            class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none"
                            :disabled="loading || aiDetecting"
                        ></textarea>
                        <p class="mt-1 text-sm text-gray-500">
                            💡 Tip: Include the year, make, model, and wheel size (if known) for best results
                        </p>
                    </div>

                    <button 
                        @click="searchWithNaturalLanguage()"
                        :disabled="!naturalLanguageQuery || naturalLanguageQuery.trim().length < 10 || loading || aiDetecting"
                        class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 text-white py-3 px-6 rounded-md hover:from-purple-700 hover:to-indigo-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-all font-medium shadow-lg flex items-center justify-center gap-2"
                    >
                        <svg x-show="!loading && !aiDetecting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                        </svg>
                        <svg x-show="loading || aiDetecting" class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span x-show="!loading && !aiDetecting">🤖 Find Tire Sizes with AI</span>
                        <span x-show="loading || aiDetecting">AI is processing your query...</span>
                    </button>
                </div>

                <!-- Error Message -->
                <div x-show="errorMessage && !showAddVehicleForm" class="mt-4 bg-red-50 border-l-4 border-red-400 p-4">
                    <p class="text-sm text-red-700" x-text="errorMessage"></p>
                </div>
                
                <!-- Add Vehicle Form -->
                <div x-show="showAddVehicleForm && vehicleToAdd" class="mt-4 bg-blue-50 border-l-4 border-blue-400 p-6 rounded-lg">
                    <h3 class="text-lg font-bold text-blue-900 mb-2">
                        <span x-show="!vehicleToAdd.front_tire">Vehicle Not Available in Database</span>
                        <span x-show="vehicleToAdd.front_tire">Confirm Tire Sizes (AI Detected)</span>
                    </h3>
                    <p class="text-sm text-blue-800 mb-4">
                        <span x-show="!vehicleToAdd.front_tire">
                            We found your vehicle (<span x-text="vehicleToAdd.year + ' ' + vehicleToAdd.make + ' ' + vehicleToAdd.model"></span>) 
                            but it's not in our database yet. Please enter your tire size to add it.
                        </span>
                        <span x-show="vehicleToAdd.front_tire">
                            We found your vehicle (<span x-text="vehicleToAdd.year + ' ' + vehicleToAdd.make + ' ' + vehicleToAdd.model"></span>) 
                            and detected tire sizes using AI. Please verify and confirm to add to database.
                        </span>
                    </p>
                    
                    <div class="space-y-4">
                        <div class="bg-white p-4 rounded border border-blue-200">
                            <p class="text-sm font-semibold text-gray-700 mb-2">Vehicle Information (from VIN):</p>
                            <div class="grid grid-cols-2 gap-2 text-sm text-gray-600">
                                <div><span class="font-medium">Year:</span> <span x-text="vehicleToAdd.year"></span></div>
                                <div><span class="font-medium">Make:</span> <span x-text="vehicleToAdd.make"></span></div>
                                <div><span class="font-medium">Model:</span> <span x-text="vehicleToAdd.model"></span></div>
                                <div x-show="vehicleToAdd.trim"><span class="font-medium">Trim:</span> <span x-text="vehicleToAdd.trim"></span></div>
                            </div>
                            
                            <!-- AI Detected Tire Sizes Section -->
                            <div x-show="vehicleToAdd && vehicleToAdd.ai_front_tire" class="mt-4 pt-4 border-t border-blue-100">
                                <p class="text-sm font-semibold text-green-700 mb-2">✨ Recommended Tire Sizes (AI Detected):</p>
                                <div class="grid grid-cols-2 gap-2 text-sm text-green-600">
                                    <div>
                                        <span class="font-medium">Front:</span> 
                                        <span x-text="vehicleToAdd.ai_front_tire" class="font-bold"></span>
                                        <span class="ml-1 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full">AI</span>
                                    </div>
                                    <div x-show="vehicleToAdd.ai_rear_tire">
                                        <span class="font-medium">Rear:</span> 
                                        <span x-text="vehicleToAdd.ai_rear_tire" class="font-bold"></span>
                                        <span class="ml-1 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full">AI</span>
                                    </div>
                                </div>
                                <p class="text-xs text-green-600 mt-2 italic">These sizes are pre-filled below. Please verify on your vehicle's tire sidewall or door jamb.</p>
                            </div>
                            
                            <!-- AI Detection Button (when tire sizes not available) -->
                            <div x-show="vehicleToAdd && !vehicleToAdd.ai_front_tire && !aiDetecting" class="mt-4 pt-4 border-t border-blue-100">
                                <p class="text-sm font-semibold text-gray-700 mb-2">🤖 Need help finding tire sizes?</p>
                                <button 
                                    @click="detectTireSizesWithAI()"
                                    :disabled="loading"
                                    class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-6 rounded-md hover:from-blue-600 hover:to-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-all font-medium shadow-md flex items-center justify-center gap-2"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    <span>Detect Tire Sizes with AI</span>
                                </button>
                                <p class="text-xs text-gray-500 mt-2 text-center">Our AI will analyze your vehicle and suggest the correct tire sizes</p>
                            </div>
                            
                            <!-- AI Detecting Loading State -->
                            <div x-show="aiDetecting" class="mt-4 pt-4 border-t border-blue-100">
                                <div class="flex items-center justify-center gap-3 py-4">
                                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span class="text-sm font-medium text-blue-600">AI is detecting tire sizes...</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label for="front_tire" class="block text-sm font-medium text-gray-700 mb-2">
                                Front Tire Size <span class="text-red-500">*</span>
                                <span x-show="vehicleToAdd && vehicleToAdd.front_tire" class="text-xs text-green-600 font-normal ml-2">(AI Detected)</span>
                            </label>
                            <input 
                                type="text" 
                                id="front_tire"
                                x-model="vehicleToAdd ? vehicleToAdd.front_tire : ''"
                                placeholder="e.g., 215/55R17"
                                pattern="\d{3}/\d{2}R\d{2}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
                                :class="vehicleToAdd && vehicleToAdd.front_tire ? 'bg-green-50 border-green-300' : ''"
                                required
                                @keyup.enter="addVehicleToDatabase()"
                            >
                            <p class="text-xs text-gray-500 mt-1">
                                <span x-show="!vehicleToAdd || !vehicleToAdd.front_tire">
                                    <strong>Where to find:</strong> Check your current tire sidewall or owner's manual. Format: 225/65R17
                                </span>
                                <span x-show="vehicleToAdd && vehicleToAdd.front_tire" class="text-green-700">
                                    ✓ Tire size detected using AI. Please verify on your vehicle's tire sidewall or door jamb.
                                </span>
                            </p>
                        </div>
                        
                        <div>
                            <label for="rear_tire" class="block text-sm font-medium text-gray-700 mb-2">
                                Rear Tire Size <span class="text-gray-500 text-xs">(Optional - only if different from front)</span>
                                <span x-show="vehicleToAdd && vehicleToAdd.rear_tire" class="text-xs text-green-600 font-normal ml-2">(AI Detected)</span>
                            </label>
                            <input 
                                type="text" 
                                id="rear_tire"
                                x-model="vehicleToAdd ? vehicleToAdd.rear_tire : ''"
                                placeholder="Leave blank if same as front"
                                pattern="\d{3}/\d{2}R\d{2}"
                                class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                :class="vehicleToAdd && vehicleToAdd.rear_tire ? 'bg-green-50 border-green-300' : ''"
                            >
                            <p class="text-xs text-gray-500 mt-1">
                                <span x-show="!vehicleToAdd || !vehicleToAdd.rear_tire">Most vehicles use the same size front and rear</span>
                                <span x-show="vehicleToAdd && vehicleToAdd.rear_tire" class="text-green-700">
                                    ✓ Staggered setup detected. Please verify on your vehicle.
                                </span>
                            </p>
                        </div>
                        
                        <div class="flex gap-2">
                            <button 
                                @click="addVehicleToDatabase()"
                                :disabled="loading || !vehicleToAdd || !vehicleToAdd.front_tire"
                                class="flex-1 bg-green-600 text-white py-3 px-6 rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors font-medium"
                            >
                                <span x-show="!loading">✓ Add Vehicle & Continue</span>
                                <span x-show="loading">Adding...</span>
                            </button>
                            <button 
                                @click="cancelAddVehicle()"
                                :disabled="loading"
                                class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50 disabled:bg-gray-100 disabled:cursor-not-allowed transition-colors"
                            >
                                Cancel
                            </button>
                        </div>
                    </div>
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
                                <span x-show="results.fitment.verified === true" class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">✓ Verified</span>
                                <span x-show="results.fitment.verified === false" class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">⚠ Estimated</span>
                                <span x-show="results.fitment.notes && results.fitment.notes.includes('AI')" class="ml-2 text-xs text-green-600 font-medium">(AI Detected)</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-600">Rear Tires:</span>
                                <span class="ml-2 text-lg font-semibold text-gray-900" x-text="results.fitment.rear_tire || results.fitment.front_tire"></span>
                                <span x-show="!results.fitment.is_staggered" class="ml-2 text-xs text-gray-500 italic">(Same as front)</span>
                                <span x-show="results.fitment.verified === true" class="ml-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs font-medium rounded-full">✓ Verified</span>
                                <span x-show="results.fitment.verified === false" class="ml-2 px-2 py-0.5 bg-yellow-100 text-yellow-800 text-xs font-medium rounded-full">⚠ Estimated</span>
                                <span x-show="results.fitment.notes && results.fitment.notes.includes('AI')" class="ml-2 text-xs text-green-600 font-medium">(AI Detected)</span>
                            </div>
                        </div>
                        <p x-show="results.fitment.is_staggered" class="text-sm text-gray-600 mt-2">
                            <em>This vehicle uses a staggered tire setup (different front and rear sizes)</em>
                        </p>
                        <p x-show="results.fitment.verified === false" class="text-xs text-yellow-700 mt-2 bg-yellow-50 p-2 rounded">
                            <strong>⚠️ Estimated Data:</strong> Tire size is estimated (trim not matched). This is the most common tire size for this vehicle model/year. Always verify on your vehicle's door placard or owner's manual.
                        </p>
                        <p x-show="results.fitment.verified === true" class="text-xs text-green-700 mt-2">
                            <strong>✓ Verified:</strong> Tire size matches your exact vehicle configuration.
                        </p>
                        <p x-show="results.fitment.notes && results.fitment.notes.includes('AI')" class="text-xs text-green-700 mt-2">
                            <strong>ℹ️ Note:</strong> Tire sizes were determined using AI. Always verify on your vehicle's tire sidewall or door jamb before purchasing.
                        </p>
                    </div>
                </div>

                <!-- Front Tires (only show if tires are available) -->
                <div x-show="results.tires.front.length > 0" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        Front Tires (<span x-text="results.fitment.front_tire"></span>)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

                <!-- Rear Tires (only show if staggered AND tires are available) -->
                <div x-show="results.fitment.is_staggered && results.tires.rear.length > 0" class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">
                        Rear Tires (<span x-text="results.fitment.rear_tire"></span>)
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
