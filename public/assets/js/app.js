/**
 * Tire Fitment Application JavaScript
 * Uses Alpine.js for reactive UI
 */

function tireFitmentApp() {
    return {
        // State
        searchMode: 'vin', // 'vin', 'ai', or 'ai-natural'
        loading: false,
        showResults: false,
        errorMessage: '',
        showAddVehicleForm: false,
        vehicleToAdd: null, // Will be initialized when needed
        aiDetecting: false, // AI tire size detection in progress
        
        // VIN search
        vinInput: '',
        
        // AI Direct Search (with dropdowns)
        years: [],
        aiYear: '',
        aiMake: '',
        aiModel: '',
        aiTrim: '',
        aiMakes: [],
        aiModels: [],
        aiTrims: [],
        
        // AI Natural Language Search
        naturalLanguageQuery: '',
        
        // Results
        results: {
            vehicle: {},
            fitment: {},
            tires: {
                front: [],
                rear: []
            }
        },
        
        // Initialize
        async init() {
            if (this.searchMode === 'ai') {
                await this.loadYears();
            }
        },
        
        // API Base URL (relative to current page)
        // Handles both local development and Render deployment
        getApiUrl(endpoint) {
            const baseUrl = window.location.origin;
            const path = window.location.pathname;
            
            // Check if we're in the public directory (handles /public/, /public, /public/index.php, etc.)
            const publicMatch = path.match(/(.*)\/public(\/|$)/);
            if (publicMatch) {
                // Extract base path (everything before /public)
                const basePath = publicMatch[1] || '';
                return baseUrl + basePath + '/api/' + endpoint;
            }
            
            // For Render: if path starts with /, API is at /api/
            // This handles root-level deployment where public/ is the document root
            return baseUrl + '/api/' + endpoint;
        },
        
        // Load years for YMM search
        async loadYears() {
            try {
                const response = await fetch(this.getApiUrl('ymm.php?action=year'));
                const data = await response.json();
                if (data.success) {
                    this.years = data.data;
                }
            } catch (error) {
                console.error('Error loading years:', error);
            }
        },
        
        // Load makes for selected year
        async loadMakes() {
            if (!this.selectedYear) {
                this.makes = [];
                this.selectedMake = '';
                this.models = [];
                this.selectedModel = '';
                this.trims = [];
                this.selectedTrim = '';
                return;
            }
            
            try {
                this.loading = true;
                const response = await fetch(
                    this.getApiUrl(`ymm.php?action=make&year=${encodeURIComponent(this.selectedYear)}`)
                );
                const data = await response.json();
                if (data.success) {
                    this.makes = data.data;
                    this.selectedMake = '';
                    this.models = [];
                    this.selectedModel = '';
                    this.trims = [];
                    this.selectedTrim = '';
                }
            } catch (error) {
                console.error('Error loading makes:', error);
                this.errorMessage = 'Failed to load makes. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Load models for selected make
        async loadModels() {
            if (!this.selectedMake) {
                this.models = [];
                this.selectedModel = '';
                this.trims = [];
                this.selectedTrim = '';
                return;
            }
            
            try {
                this.loading = true;
                const response = await fetch(
                    this.getApiUrl(
                        `ymm.php?action=model&year=${encodeURIComponent(this.selectedYear)}&make=${encodeURIComponent(this.selectedMake)}`
                    )
                );
                const data = await response.json();
                if (data.success) {
                    this.models = data.data;
                    this.selectedModel = '';
                    this.trims = [];
                    this.selectedTrim = '';
                }
            } catch (error) {
                console.error('Error loading models:', error);
                this.errorMessage = 'Failed to load models. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Load trims for selected model
        async loadTrims() {
            if (!this.selectedModel) {
                this.trims = [];
                this.selectedTrim = '';
                return;
            }
            
            try {
                this.loading = true;
                const response = await fetch(
                    this.getApiUrl(
                        `ymm.php?action=trim&year=${encodeURIComponent(this.selectedYear)}&make=${encodeURIComponent(this.selectedMake)}&model=${encodeURIComponent(this.selectedModel)}`
                    )
                );
                const data = await response.json();
                if (data.success) {
                    this.trims = data.data;
                    this.selectedTrim = '';
                }
            } catch (error) {
                console.error('Error loading trims:', error);
                this.errorMessage = 'Failed to load trims. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Load makes for AI search (same as YMM but for AI fields)
        async loadAIMakes() {
            if (!this.aiYear) {
                this.aiMakes = [];
                this.aiMake = '';
                this.aiModels = [];
                this.aiModel = '';
                this.aiTrims = [];
                this.aiTrim = '';
                return;
            }
            
            try {
                this.loading = true;
                const response = await fetch(
                    this.getApiUrl(`ymm.php?action=make&year=${encodeURIComponent(this.aiYear)}`)
                );
                const data = await response.json();
                if (data.success) {
                    this.aiMakes = data.data;
                    this.aiMake = '';
                    this.aiModels = [];
                    this.aiModel = '';
                    this.aiTrims = [];
                    this.aiTrim = '';
                }
            } catch (error) {
                console.error('Error loading makes for AI search:', error);
                this.errorMessage = 'Failed to load makes. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Load models for AI search
        async loadAIModels() {
            if (!this.aiMake) {
                this.aiModels = [];
                this.aiModel = '';
                this.aiTrims = [];
                this.aiTrim = '';
                return;
            }
            
            try {
                this.loading = true;
                const response = await fetch(
                    this.getApiUrl(
                        `ymm.php?action=model&year=${encodeURIComponent(this.aiYear)}&make=${encodeURIComponent(this.aiMake)}`
                    )
                );
                const data = await response.json();
                if (data.success) {
                    this.aiModels = data.data;
                    this.aiModel = '';
                    this.aiTrims = [];
                    this.aiTrim = '';
                }
            } catch (error) {
                console.error('Error loading models for AI search:', error);
                this.errorMessage = 'Failed to load models. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Load trims for AI search
        async loadAITrims() {
            if (!this.aiModel) {
                this.aiTrims = [];
                this.aiTrim = '';
                return;
            }
            
            try {
                this.loading = true;
                const response = await fetch(
                    this.getApiUrl(
                        `ymm.php?action=trim&year=${encodeURIComponent(this.aiYear)}&make=${encodeURIComponent(this.aiMake)}&model=${encodeURIComponent(this.aiModel)}`
                    )
                );
                const data = await response.json();
                if (data.success) {
                    this.aiTrims = data.data;
                    this.aiTrim = '';
                }
            } catch (error) {
                console.error('Error loading trims for AI search:', error);
                this.errorMessage = 'Failed to load trims. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Search by VIN
        async searchByVIN() {
            if (this.vinInput.length !== 17) {
                this.errorMessage = 'VIN must be exactly 17 characters.';
                return;
            }
            
            try {
                this.loading = true;
                this.errorMessage = '';
                
                // Decode VIN
                const vinResponse = await fetch(this.getApiUrl('vin.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ vin: this.vinInput })
                });
                
                const vinData = await vinResponse.json();
                
                // Debug: Log the response to see what we're getting
                console.log('VIN API Response:', vinData);
                
                if (!vinData.success) {
                    // Check if it's a VIN validation error
                    if (vinData.message && vinData.message.includes('not valid')) {
                        this.errorMessage = 'Entered VIN is not valid. Please check the VIN and try again.';
                        this.showAddVehicleForm = false;
                    } else {
                        // VIN decode failed - suggest using YMM search with AI
                        this.errorMessage = vinData.message || 'Failed to decode VIN. Please verify the VIN is correct or use Year/Make/Model search below - you can use AI to detect tire sizes!';
                        // Switch to YMM mode to help user
                        this.searchMode = 'ymm';
                        this.loadYears();
                    }
                    return;
                }
                
                // If multiple trims available, prompt user (for now, use first trim or empty)
                // In a real app, you might show a trim selector
                const vehicle = vinData.data.vehicle;
                const trims = vinData.data.trims || [];
                const aiTireSizes = vinData.data.tire_sizes || null;
                
                // Debug: Log AI tire sizes
                console.log('AI Tire Sizes from API:', aiTireSizes);
                
                // If AI provided tire sizes, show them immediately
                if (aiTireSizes) {
                    // Show vehicle info with AI tire sizes
                    this.results = {
                        vehicle: vehicle,
                        fitment: {
                            front_tire: aiTireSizes.front_tire,
                            rear_tire: aiTireSizes.rear_tire || aiTireSizes.front_tire,
                            is_staggered: aiTireSizes.is_staggered || false,
                            notes: 'Tire sizes determined using AI from VIN decode'
                        },
                        tires: {
                            front: [],
                            rear: []
                        }
                    };
                    
                    // Update selected values
                    this.selectedYear = vehicle.year;
                    this.selectedMake = vehicle.make;
                    this.selectedModel = vehicle.model;
                    this.trims = trims;
                    
                    // Pre-fill vehicle info for adding to database (with AI tire sizes)
                    this.vehicleToAdd = {
                        year: vehicle.year,
                        make: vehicle.make,
                        model: vehicle.model,
                        trim: vehicle.trim || (trims.length === 1 ? trims[0] : null),
                        body_class: vehicle.body_class || '',
                        drive_type: vehicle.drive_type || '',
                        front_tire: aiTireSizes.front_tire,
                        rear_tire: aiTireSizes.rear_tire || ''
                    };
                    
                    // Try to search for tires in database
                    const trimToUse = trims.length === 1 ? trims[0] : (this.selectedTrim || null);
                    const tiresResponse = await fetch(
                        this.getApiUrl(
                            `tires.php?year=${vehicle.year}&make=${encodeURIComponent(vehicle.make)}&model=${encodeURIComponent(vehicle.model)}${trimToUse ? '&trim=' + encodeURIComponent(trimToUse) : ''}`
                        )
                    );
                    
                    const tiresData = await tiresResponse.json();
                    
                    if (tiresData.success && tiresData.data.tires) {
                        // Found tires in database - merge with AI results
                        this.results.tires = tiresData.data.tires;
                    } else {
                        // No tires in database, but we have AI tire sizes
                        // Show option to add vehicle with AI tire sizes
                        this.showAddVehicleForm = true;
                    }
                    
                    this.showResults = true;
                    return;
                }
                
                // No AI tire sizes - proceed with normal database lookup
                // Try to get tires with trim if only one trim, otherwise without trim
                let trimToUse = trims.length === 1 ? trims[0] : (this.selectedTrim || null);
                
                // If no trim selected and multiple available, we'll search without trim
                const tiresResponse = await fetch(
                    this.getApiUrl(
                        `tires.php?year=${vehicle.year}&make=${encodeURIComponent(vehicle.make)}&model=${encodeURIComponent(vehicle.model)}${trimToUse ? '&trim=' + encodeURIComponent(trimToUse) : ''}`
                    )
                );
                
                const tiresData = await tiresResponse.json();
                
                if (!tiresData.success) {
                    // Check if vehicle not found in database
                    if (tiresData.errors && tiresData.errors.vehicle_not_found) {
                        this.errorMessage = '';
                        // Pre-fill vehicle info from VIN decode, including AI tire sizes if available
                        console.log('Vehicle not found. AI tire sizes available:', aiTireSizes);
                        this.vehicleToAdd = {
                            year: vehicle.year,
                            make: vehicle.make,
                            model: vehicle.model,
                            trim: trimToUse || null,
                            body_class: vehicle.body_class || '',
                            drive_type: vehicle.drive_type || '',
                            front_tire: aiTireSizes && aiTireSizes.front_tire ? aiTireSizes.front_tire : '',
                            rear_tire: aiTireSizes && aiTireSizes.rear_tire ? aiTireSizes.rear_tire : '',
                            ai_front_tire: aiTireSizes && aiTireSizes.front_tire ? aiTireSizes.front_tire : null,
                            ai_rear_tire: aiTireSizes && aiTireSizes.rear_tire ? aiTireSizes.rear_tire : null
                        };
                        console.log('vehicleToAdd set to:', this.vehicleToAdd);
                        this.showAddVehicleForm = true;
                    } else {
                        this.errorMessage = tiresData.message || 'Failed to find tire matches.';
                        this.showAddVehicleForm = false;
                    }
                    return;
                }
                
                // Update selected values for potential trim selection
                this.selectedYear = vehicle.year;
                this.selectedMake = vehicle.make;
                this.selectedModel = vehicle.model;
                this.trims = trims;
                
                // If multiple trims, we should ideally show a selector
                // For now, store results and let user know they can select trim
                this.results = tiresData.data;
                this.showResults = true;
                
                // If multiple trims, show a message
                if (trims.length > 1 && !trimToUse) {
                    this.errorMessage = `Multiple trims available for this vehicle. Please select a trim for accurate fitment.`;
                    this.searchMode = 'ymm';
                }
                
            } catch (error) {
                console.error('Error searching by VIN:', error);
                this.errorMessage = 'An error occurred while processing your request. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Search by YMM
        async searchByYMM() {
            if (!this.selectedYear || !this.selectedMake || !this.selectedModel) {
                this.errorMessage = 'Please select Year, Make, and Model.';
                return;
            }
            
            try {
                this.loading = true;
                this.errorMessage = '';
                
                const url = new URL(this.getApiUrl('tires.php'));
                url.searchParams.append('year', this.selectedYear);
                url.searchParams.append('make', this.selectedMake);
                url.searchParams.append('model', this.selectedModel);
                if (this.selectedTrim) {
                    url.searchParams.append('trim', this.selectedTrim);
                }
                
                const response = await fetch(url);
                const data = await response.json();
                
                if (!data.success) {
                    // Check if vehicle not found in database
                    if (data.errors && data.errors.vehicle_not_found) {
                        this.errorMessage = '';
                        // Pre-fill vehicle info from YMM selection
                        this.vehicleToAdd = {
                            year: parseInt(this.selectedYear),
                            make: this.selectedMake,
                            model: this.selectedModel,
                            trim: this.selectedTrim || null,
                            front_tire: '',
                            rear_tire: ''
                        };
                        this.showAddVehicleForm = true;
                    } else {
                        this.errorMessage = data.message || 'Failed to find tire matches.';
                        this.showAddVehicleForm = false;
                    }
                    return;
                }
                
                this.results = data.data;
                this.showResults = true;
                this.showAddVehicleForm = false;
                
            } catch (error) {
                console.error('Error searching by YMM:', error);
                this.errorMessage = 'An error occurred while processing your request. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Reset form
        resetForm() {
            this.vinInput = '';
            this.naturalLanguageQuery = '';
            // Reset AI Direct Search fields
            this.aiYear = '';
            this.aiMake = '';
            this.aiModel = '';
            this.aiTrim = '';
            this.aiMakes = [];
            this.aiModels = [];
            this.aiTrims = [];
            this.errorMessage = '';
            this.showAddVehicleForm = false;
            this.vehicleToAdd = null;
            if (this.searchMode === 'ai') {
                this.loadYears();
            }
        },
        
        // Reset search and show form again
        resetSearch() {
            this.showResults = false;
            this.results = {
                vehicle: {},
                fitment: {},
                tires: {
                    front: [],
                    rear: []
                }
            };
            this.resetForm();
        },
        
        // Add vehicle to database
        async addVehicleToDatabase() {
            if (!this.vehicleToAdd) {
                return;
            }
            
            // Validate front tire size format
            const tireSizePattern = /^\d{3}\/\d{2}R\d{2}$/;
            if (!this.vehicleToAdd.front_tire || !tireSizePattern.test(this.vehicleToAdd.front_tire.trim())) {
                this.errorMessage = 'Please enter a valid front tire size (e.g., 215/55R17). Check your tire sidewall for the size.';
                return;
            }
            
            // Validate rear tire size if provided
            if (this.vehicleToAdd.rear_tire && !tireSizePattern.test(this.vehicleToAdd.rear_tire.trim())) {
                this.errorMessage = 'Please enter a valid rear tire size (e.g., 255/40R18) or leave it blank.';
                return;
            }
            
            try {
                this.loading = true;
                this.errorMessage = '';
                
                const response = await fetch(this.getApiUrl('add-vehicle.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        year: this.vehicleToAdd.year,
                        make: this.vehicleToAdd.make,
                        model: this.vehicleToAdd.model,
                        trim: this.vehicleToAdd.trim || null,
                        front_tire: this.vehicleToAdd.front_tire.trim(),
                        rear_tire: this.vehicleToAdd.rear_tire ? this.vehicleToAdd.rear_tire.trim() : null,
                        notes: 'User added vehicle via VIN decode'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    this.errorMessage = '';
                    this.showAddVehicleForm = false;
                    
                    // Show appropriate message
                    if (data.data && data.data.already_exists) {
                        this.errorMessage = 'Vehicle already exists in database. Searching for tires...';
                    } else {
                        this.errorMessage = 'Vehicle added successfully! Searching for tires...';
                    }
                    
                    // Automatically search for tires
                    if (this.searchMode === 'vin') {
                        // Re-use the decoded vehicle info to search for tires
                        const tiresResponse = await fetch(
                            this.getApiUrl(
                                `tires.php?year=${this.vehicleToAdd.year}&make=${encodeURIComponent(this.vehicleToAdd.make)}&model=${encodeURIComponent(this.vehicleToAdd.model)}${this.vehicleToAdd.trim ? '&trim=' + encodeURIComponent(this.vehicleToAdd.trim) : ''}`
                            )
                        );
                        
                        const tiresData = await tiresResponse.json();
                        
                        if (tiresData.success) {
                            this.errorMessage = '';
                            this.results = tiresData.data;
                            this.showResults = true;
                            // Update selected values
                            this.selectedYear = this.vehicleToAdd.year;
                            this.selectedMake = this.vehicleToAdd.make;
                            this.selectedModel = this.vehicleToAdd.model;
                        } else {
                            // Check if vehicle not found (shouldn't happen if we just added it)
                            if (tiresData.errors && tiresData.errors.vehicle_not_found) {
                                this.errorMessage = 'Vehicle added, but no matching tires found in inventory.';
                            } else {
                                this.errorMessage = tiresData.message || 'Vehicle added, but unable to find tires.';
                            }
                        }
                    } else {
                        await this.searchByYMM();
                    }
                } else {
                    // Handle error response
                    if (response.status === 409 || (data.message && data.message.includes('already exists'))) {
                        // Vehicle already exists - just search for tires
                        this.errorMessage = 'Vehicle already exists. Searching for tires...';
                        this.showAddVehicleForm = false;
                        
                        if (this.searchMode === 'vin') {
                            const tiresResponse = await fetch(
                                this.getApiUrl(
                                    `tires.php?year=${this.vehicleToAdd.year}&make=${encodeURIComponent(this.vehicleToAdd.make)}&model=${encodeURIComponent(this.vehicleToAdd.model)}${this.vehicleToAdd.trim ? '&trim=' + encodeURIComponent(this.vehicleToAdd.trim) : ''}`
                                )
                            );
                            const tiresData = await tiresResponse.json();
                            if (tiresData.success) {
                                this.errorMessage = '';
                                this.results = tiresData.data;
                                this.showResults = true;
                            } else {
                                this.errorMessage = 'Vehicle exists, but no matching tires found in inventory.';
                            }
                        }
                    } else {
                        this.errorMessage = data.message || 'Failed to add vehicle to database.';
                    }
                }
            } catch (error) {
                console.error('Error adding vehicle:', error);
                this.errorMessage = 'An error occurred while adding the vehicle. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        
        // Cancel add vehicle form
        cancelAddVehicle() {
            this.showAddVehicleForm = false;
            this.vehicleToAdd = null;
            this.aiDetecting = false;
            this.errorMessage = '';
        },
        
        // Detect tire sizes using AI
        async detectTireSizesWithAI() {
            if (!this.vehicleToAdd) {
                console.error('vehicleToAdd is null');
                return;
            }
            
            try {
                this.aiDetecting = true;
                this.errorMessage = '';
                
                console.log('Calling AI detection for:', this.vehicleToAdd);
                
                // Call dedicated AI detection endpoint
                const aiResponse = await fetch(this.getApiUrl('detect-tire-sizes.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        year: this.vehicleToAdd.year,
                        make: this.vehicleToAdd.make,
                        model: this.vehicleToAdd.model,
                        trim: this.vehicleToAdd.trim || '',
                        body_class: this.vehicleToAdd.body_class || '',
                        drive_type: this.vehicleToAdd.drive_type || ''
                    })
                });
                
                // Check if response is OK
                if (!aiResponse.ok) {
                    const errorText = await aiResponse.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { success: false, message: `HTTP ${aiResponse.status}: ${errorText.substring(0, 100)}` };
                    }
                    console.error('AI Detection HTTP Error:', aiResponse.status, errorData);
                    this.errorMessage = errorData.message || `AI detection failed (HTTP ${aiResponse.status}). Please enter tire sizes manually.`;
                    return;
                }
                
                const aiData = await aiResponse.json();
                
                console.log('AI Detection Response:', aiData);
                
                if (aiData.success && aiData.data) {
                    const aiTireSizes = aiData.data;
                    
                    // Update vehicleToAdd with AI-detected tire sizes
                    this.vehicleToAdd.front_tire = aiTireSizes.front_tire || '';
                    this.vehicleToAdd.rear_tire = aiTireSizes.rear_tire || '';
                    this.vehicleToAdd.ai_front_tire = aiTireSizes.front_tire || null;
                    this.vehicleToAdd.ai_rear_tire = aiTireSizes.rear_tire || null;
                    
                    console.log('AI tire sizes detected and applied:', aiTireSizes);
                } else {
                    // AI tire sizes not available
                    this.errorMessage = aiData.message || 'AI tire size detection is temporarily unavailable. Please enter tire sizes manually or check your vehicle\'s door placard.';
                }
            } catch (error) {
                console.error('AI detection error:', error);
                this.errorMessage = 'Failed to detect tire sizes with AI. Please enter tire sizes manually.';
            } finally {
                this.aiDetecting = false;
            }
        },
        
        // AI Direct Search - uses dropdowns to call AI
        async searchWithAI() {
            if (!this.aiYear || !this.aiMake || !this.aiModel) {
                this.errorMessage = 'Please select Year, Make, and Model.';
                return;
            }
            
            try {
                this.loading = true;
                this.aiDetecting = true;
                this.errorMessage = '';
                this.showResults = false;
                this.showAddVehicleForm = false;
                
                console.log('AI Direct Search for:', {
                    year: this.aiYear,
                    make: this.aiMake,
                    model: this.aiModel,
                    trim: this.aiTrim
                });
                
                // Call AI detection endpoint directly
                const aiResponse = await fetch(this.getApiUrl('detect-tire-sizes.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        year: parseInt(this.aiYear),
                        make: this.aiMake.trim(),
                        model: this.aiModel.trim(),
                        trim: this.aiTrim ? this.aiTrim.trim() : '',
                        body_class: '',
                        drive_type: ''
                    })
                });
                
                if (!aiResponse.ok) {
                    const errorText = await aiResponse.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { success: false, message: `HTTP ${aiResponse.status}: ${errorText.substring(0, 100)}` };
                    }
                    console.error('AI Detection Error:', errorData);
                    this.errorMessage = errorData.message || 'AI tire size detection failed. Please try again or enter tire sizes manually.';
                    return;
                }
                
                const aiData = await aiResponse.json();
                console.log('AI Detection Response:', aiData);
                
                if (aiData.success && aiData.data && aiData.data.front_tire) {
                    // Show results with AI-detected tire sizes
                    this.results = {
                        vehicle: {
                            year: parseInt(this.aiYear),
                            make: this.aiMake.trim(),
                            model: this.aiModel.trim(),
                            trim: this.aiTrim ? this.aiTrim.trim() : null
                        },
                        fitment: {
                            front_tire: aiData.data.front_tire,
                            rear_tire: aiData.data.rear_tire || aiData.data.front_tire,
                            is_staggered: aiData.data.rear_tire && aiData.data.rear_tire !== aiData.data.front_tire,
                            notes: 'Tire sizes detected using AI',
                            verified: true,
                            source: 'ai'
                        },
                        tires: {
                            front: [],
                            rear: []
                        }
                    };
                    
                    // Try to find matching tires in database
                    try {
                        const tiresResponse = await fetch(
                            this.getApiUrl(
                                `tires.php?year=${this.aiYear}&make=${encodeURIComponent(this.aiMake)}&model=${encodeURIComponent(this.aiModel)}${this.aiTrim ? '&trim=' + encodeURIComponent(this.aiTrim) : ''}`
                            )
                        );
                        const tiresData = await tiresResponse.json();
                        
                        if (tiresData.success && tiresData.data && tiresData.data.tires) {
                            this.results.tires = tiresData.data.tires;
                        }
                    } catch (tireError) {
                        console.log('Could not find tires in database, showing AI results only');
                    }
                    
                    this.showResults = true;
                    this.errorMessage = '';
                } else {
                    this.errorMessage = aiData.message || 'AI could not detect tire sizes for this vehicle. Please check your vehicle information or enter tire sizes manually.';
                }
                
            } catch (error) {
                console.error('AI Direct Search Error:', error);
                this.errorMessage = 'An error occurred during AI tire size detection. Please try again.';
            } finally {
                this.loading = false;
                this.aiDetecting = false;
            }
        },
        
        // AI Direct Search - uses dropdowns to call AI
        async searchWithAI() {
            if (!this.aiYear || !this.aiMake || !this.aiModel) {
                this.errorMessage = 'Please select Year, Make, and Model.';
                return;
            }
            
            try {
                this.loading = true;
                this.aiDetecting = true;
                this.errorMessage = '';
                this.showResults = false;
                this.showAddVehicleForm = false;
                
                console.log('AI Direct Search for:', {
                    year: this.aiYear,
                    make: this.aiMake,
                    model: this.aiModel,
                    trim: this.aiTrim
                });
                
                // Call AI detection endpoint directly
                const aiResponse = await fetch(this.getApiUrl('detect-tire-sizes.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        year: parseInt(this.aiYear),
                        make: this.aiMake.trim(),
                        model: this.aiModel.trim(),
                        trim: this.aiTrim ? this.aiTrim.trim() : '',
                        body_class: '',
                        drive_type: ''
                    })
                });
                
                if (!aiResponse.ok) {
                    const errorText = await aiResponse.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { success: false, message: `HTTP ${aiResponse.status}: ${errorText.substring(0, 100)}` };
                    }
                    console.error('AI Detection Error:', errorData);
                    this.errorMessage = errorData.message || 'AI tire size detection failed. Please try again or enter tire sizes manually.';
                    return;
                }
                
                const aiData = await aiResponse.json();
                console.log('AI Detection Response:', aiData);
                
                if (aiData.success && aiData.data && aiData.data.front_tire) {
                    // Show results with AI-detected tire sizes
                    this.results = {
                        vehicle: {
                            year: parseInt(this.aiYear),
                            make: this.aiMake.trim(),
                            model: this.aiModel.trim(),
                            trim: this.aiTrim ? this.aiTrim.trim() : null
                        },
                        fitment: {
                            front_tire: aiData.data.front_tire,
                            rear_tire: aiData.data.rear_tire || aiData.data.front_tire,
                            is_staggered: aiData.data.rear_tire && aiData.data.rear_tire !== aiData.data.front_tire,
                            notes: 'Tire sizes detected using AI',
                            verified: true,
                            source: 'ai'
                        },
                        tires: {
                            front: [],
                            rear: []
                        }
                    };
                    
                    // Try to find matching tires in database
                    try {
                        const tiresResponse = await fetch(
                            this.getApiUrl(
                                `tires.php?year=${this.aiYear}&make=${encodeURIComponent(this.aiMake)}&model=${encodeURIComponent(this.aiModel)}${this.aiTrim ? '&trim=' + encodeURIComponent(this.aiTrim) : ''}`
                            )
                        );
                        const tiresData = await tiresResponse.json();
                        
                        if (tiresData.success && tiresData.data && tiresData.data.tires) {
                            this.results.tires = tiresData.data.tires;
                        }
                    } catch (tireError) {
                        console.log('Could not find tires in database, showing AI results only');
                    }
                    
                    this.showResults = true;
                    this.errorMessage = '';
                } else {
                    this.errorMessage = aiData.message || 'AI could not detect tire sizes for this vehicle. Please check your vehicle information or enter tire sizes manually.';
                }
                
            } catch (error) {
                console.error('AI Direct Search Error:', error);
                this.errorMessage = 'An error occurred during AI tire size detection. Please try again.';
            } finally {
                this.loading = false;
                this.aiDetecting = false;
            }
        },
        
        // AI Natural Language Search - processes natural language queries
        async searchWithNaturalLanguage() {
            if (!this.naturalLanguageQuery || this.naturalLanguageQuery.trim().length < 10) {
                this.errorMessage = 'Please enter a detailed question about tire sizes (at least 10 characters).';
                return;
            }
            
            try {
                this.loading = true;
                this.aiDetecting = true;
                this.errorMessage = '';
                this.showResults = false;
                this.showAddVehicleForm = false;
                
                console.log('AI Natural Language Query:', this.naturalLanguageQuery);
                
                // Call AI natural language endpoint
                const aiResponse = await fetch(this.getApiUrl('ai-natural-language.php'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        query: this.naturalLanguageQuery.trim()
                    })
                });
                
                if (!aiResponse.ok) {
                    const errorText = await aiResponse.text();
                    let errorData;
                    try {
                        errorData = JSON.parse(errorText);
                    } catch (e) {
                        errorData = { success: false, message: `HTTP ${aiResponse.status}: ${errorText.substring(0, 100)}` };
                    }
                    console.error('AI Natural Language Error:', errorData);
                    this.errorMessage = errorData.message || 'AI could not process your query. Please try rephrasing your question.';
                    return;
                }
                
                const aiData = await aiResponse.json();
                console.log('AI Natural Language Response:', aiData);
                
                if (aiData.success && aiData.data && aiData.data.front_tire) {
                    // Show results with AI-detected tire sizes
                    this.results = {
                        vehicle: {
                            year: aiData.data.year || null,
                            make: aiData.data.make || null,
                            model: aiData.data.model || null,
                            trim: aiData.data.trim || null
                        },
                        fitment: {
                            front_tire: aiData.data.front_tire,
                            rear_tire: aiData.data.rear_tire || aiData.data.front_tire,
                            is_staggered: aiData.data.is_staggered || false,
                            notes: aiData.data.explanation || 'Tire sizes detected using AI natural language processing',
                            verified: true,
                            source: 'ai_natural_language',
                            wheel_size: aiData.data.wheel_size || null
                        },
                        tires: {
                            front: [],
                            rear: []
                        }
                    };
                    
                    // Try to find matching tires in database if we have vehicle info
                    if (aiData.data.year && aiData.data.make && aiData.data.model) {
                        try {
                            const tiresResponse = await fetch(
                                this.getApiUrl(
                                    `tires.php?year=${aiData.data.year}&make=${encodeURIComponent(aiData.data.make)}&model=${encodeURIComponent(aiData.data.model)}${aiData.data.trim ? '&trim=' + encodeURIComponent(aiData.data.trim) : ''}`
                                )
                            );
                            const tiresData = await tiresResponse.json();
                            
                            if (tiresData.success && tiresData.data && tiresData.data.tires) {
                                this.results.tires = tiresData.data.tires;
                            }
                        } catch (tireError) {
                            console.log('Could not find tires in database, showing AI results only');
                        }
                    }
                    
                    this.showResults = true;
                    this.errorMessage = '';
                } else {
                    this.errorMessage = aiData.message || 'AI could not determine tire sizes from your query. Please try rephrasing or be more specific about the vehicle year, make, and model.';
                }
                
            } catch (error) {
                console.error('AI Natural Language Search Error:', error);
                this.errorMessage = 'An error occurred while processing your query. Please try again.';
            } finally {
                this.loading = false;
                this.aiDetecting = false;
            }
        },
        
        // Safe getter for vehicleToAdd properties
        getVehicleProperty(property, defaultValue = '') {
            return (this.vehicleToAdd && this.vehicleToAdd[property]) ? this.vehicleToAdd[property] : defaultValue;
        },
        
        // Request quote (placeholder - integrate with your quote system)
        requestQuote(tire) {
            alert(`Quote requested for:\n\n${tire.brand} ${tire.model}\nSize: ${tire.tire_size}\nPrice: $${parseFloat(tire.price).toFixed(2)}\n\nThis would typically open a quote form or send an email.`);
        },
        
        // Add to cart (placeholder - integrate with your cart system)
        addToCart(tire, position) {
            // Example: Integrate with your e-commerce cart system
            // This could be WooCommerce, Magento, custom cart, etc.
            
            // Example for custom cart:
            // const cartItem = {
            //     id: tire.id,
            //     brand: tire.brand,
            //     model: tire.model,
            //     size: tire.tire_size,
            //     position: position,
            //     price: tire.price,
            //     quantity: 1
            // };
            // 
            // // Add to cart via API or localStorage
            // fetch('/api/cart.php', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(cartItem)
            // });
            
            alert(`Added to cart:\n\n${tire.brand} ${tire.model}\nSize: ${tire.tire_size}\nPosition: ${position}\nPrice: $${parseFloat(tire.price).toFixed(2)}\n\nThis would typically add the item to your shopping cart.`);
        }
    };
}
