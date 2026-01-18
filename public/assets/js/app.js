/**
 * Tire Fitment Application JavaScript
 * Uses Alpine.js for reactive UI
 */

function tireFitmentApp() {
    return {
        // State
        searchMode: 'vin', // 'vin' or 'ymm'
        loading: false,
        showResults: false,
        errorMessage: '',
        
        // VIN search
        vinInput: '',
        
        // YMM search
        years: [],
        makes: [],
        models: [],
        trims: [],
        selectedYear: '',
        selectedMake: '',
        selectedModel: '',
        selectedTrim: '',
        
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
            if (this.searchMode === 'ymm') {
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
                
                if (!vinData.success) {
                    this.errorMessage = vinData.message || 'Failed to decode VIN.';
                    return;
                }
                
                // If multiple trims available, prompt user (for now, use first trim or empty)
                // In a real app, you might show a trim selector
                const vehicle = vinData.data.vehicle;
                const trims = vinData.data.trims || [];
                
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
                    this.errorMessage = tiresData.message || 'Failed to find tire matches.';
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
                    this.errorMessage = data.message || 'Failed to find tire matches.';
                    return;
                }
                
                this.results = data.data;
                this.showResults = true;
                
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
            this.selectedYear = '';
            this.selectedMake = '';
            this.selectedModel = '';
            this.selectedTrim = '';
            this.makes = [];
            this.models = [];
            this.trims = [];
            this.errorMessage = '';
            if (this.searchMode === 'ymm') {
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
