// QueueingPro - Simple and Reliable Queue Management System JavaScri    bindEvents() {
        // Transaction card clicks
        const cards = document.querySelectorAll('.transaction-card');
        console.log('Found cards:', cards.length);
        
        cards.forEach((card, index) => {
            console.log(`Binding card ${index}:`, card.getAttribute('data-category'));
            card.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const category = card.getAttribute('data-category');
                console.log('Card clicked, category:', category);
                this.showConfirmation(category); // Show confirmation instead of generating ticket directly
            });
        });
        
        // Modal close events
        this.bindModalEvents();
        this.bindConfirmationEvents();
    }
    
    bindConfirmationEvents() {
        const confirmationModal = document.getElementById('confirmationModal');
        const closeConfirmation = document.getElementById('closeConfirmation');
        const cancelService = document.getElementById('cancelService');
        const confirmService = document.getElementById('confirmService');
        
        if (closeConfirmation) {
            closeConfirmation.addEventListener('click', () => this.closeConfirmation());
        }
        
        if (cancelService) {
            cancelService.addEventListener('click', () => this.closeConfirmation());
        }
        
        if (confirmService) {
            confirmService.addEventListener('click', () => this.proceedWithService());
        }
        
        if (confirmationModal) {
            confirmationModal.addEventListener('click', (e) => {
                if (e.target === confirmationModal) {
                    this.closeConfirmation();
                }
            });
        }
    }ystem {
    constructor() {
        this.currentNumbers = {
            general: 1,
            deposit: 1,
            loans: 1,
            account: 1,
            business: 1,
            support: 1
        };
        
        this.queueCategories = {
            general: { 
                prefix: 'A', 
                name: 'General Banking', 
                waitTime: '5-10 minutes',
                description: 'Account inquiries, statements, general assistance',
                icon: 'fas fa-university'
            },
            deposit: { 
                prefix: 'B', 
                name: 'Deposits & Withdrawals', 
                waitTime: '3-7 minutes',
                description: 'Cash deposits, withdrawals, money transfers',
                icon: 'fas fa-money-bill-wave'
            },
            loans: { 
                prefix: 'C', 
                name: 'Loans & Credit', 
                waitTime: '15-25 minutes',
                description: 'Loan applications, credit cards, mortgage',
                icon: 'fas fa-handshake'
            },
            account: { 
                prefix: 'D', 
                name: 'Account Services', 
                waitTime: '10-20 minutes',
                description: 'Account opening, closures, modifications',
                icon: 'fas fa-user-cog'
            },
            business: { 
                prefix: 'E', 
                name: 'Business Banking', 
                waitTime: '12-20 minutes',
                description: 'Business accounts, merchant services',
                icon: 'fas fa-briefcase'
            },
            support: { 
                prefix: 'F', 
                name: 'Customer Support', 
                waitTime: '8-15 minutes',
                description: 'Complaints, feedback, technical support',
                icon: 'fas fa-headset'
            }
        };
        
        this.selectedCategory = null;
        
        console.log('QueueingSystem initialized');
        this.init();
    }
    
    init() {
        console.log('Initializing system...');
        this.bindEvents();
        console.log('Events bound successfully');
    }
    
    bindEvents() {
        // Transaction card clicks - Simple approach
        const cards = document.querySelectorAll('.transaction-card');
        console.log('Found cards:', cards.length);
        
        cards.forEach((card, index) => {
            console.log(`Binding card ${index}:`, card.getAttribute('data-category'));
            card.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const category = card.getAttribute('data-category');
                console.log('Card clicked, category:', category);
                this.generateTicket(category);
            });
        });
        
        // Modal close events
        this.bindModalEvents();
    }
    
    bindModalEvents() {
        const modal = document.getElementById('ticketModal');
        const closeBtn = document.getElementById('closeModal');
        const closeTicketBtn = document.getElementById('closeTicket');
        const printBtn = document.getElementById('printTicket');
        const photoBtn = document.getElementById('takePhoto');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeModal());
        }
        
        if (closeTicketBtn) {
            closeTicketBtn.addEventListener('click', () => this.closeModal());
        }
        
        if (printBtn) {
            printBtn.addEventListener('click', () => this.printTicket());
        }
        
        if (photoBtn) {
            photoBtn.addEventListener('click', () => this.takePhoto());
        }
        
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeModal();
                }
            });
        }
        
        // Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeModal();
            }
        });
    }
    
    generateTicket(category) {
        console.log('Generating ticket for:', category);
        
        if (!this.queueCategories[category]) {
            console.error('Invalid category:', category);
            return;
        }
        
        const categoryInfo = this.queueCategories[category];
        const ticketNumber = `${categoryInfo.prefix}${this.currentNumbers[category].toString().padStart(3, '0')}`;
        
        console.log('Ticket number:', ticketNumber);
        
        // Increment counter
        this.currentNumbers[category]++;
        
        // Update modal content
        this.updateTicketModal(ticketNumber, categoryInfo);
        
        // Show modal
        this.showModal();
    }
    
    updateTicketModal(ticketNumber, categoryInfo) {
        console.log('Updating modal with:', ticketNumber, categoryInfo.name);
        
        const ticketNumEl = document.getElementById('ticketNumber');
        const serviceEl = document.getElementById('ticketService');
        const waitEl = document.getElementById('ticketWait');
        const aheadEl = document.getElementById('ticketAhead');
        const dateEl = document.getElementById('ticketDate');
        
        if (ticketNumEl) ticketNumEl.textContent = ticketNumber;
        if (serviceEl) serviceEl.textContent = categoryInfo.name;
        if (waitEl) waitEl.textContent = categoryInfo.waitTime;
        if (aheadEl) aheadEl.textContent = Math.floor(Math.random() * 8) + 1;
        
        if (dateEl) {
            const now = new Date();
            dateEl.textContent = now.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
    
    showModal() {
        console.log('Showing modal...');
        const modal = document.getElementById('ticketModal');
        
        if (modal) {
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            console.log('Modal displayed');
            
            // Focus the close button
            const closeBtn = document.getElementById('closeModal');
            if (closeBtn) {
                setTimeout(() => closeBtn.focus(), 100);
            }
        } else {
            console.error('Modal element not found!');
        }
    }
    
    closeModal() {
        console.log('Closing modal...');
        const modal = document.getElementById('ticketModal');
        
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            console.log('Modal closed');
        }
    }
    
    printTicket() {
        console.log('Print ticket requested');
        alert('Print function would be implemented here. For now, please take a photo of your ticket.');
    }
    
    takePhoto() {
        console.log('Take photo requested');
        alert('Camera function would be implemented here. Please take a screenshot of your ticket.');
    }
}

// Simple initialization - wait for DOM to be ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeSystem);
} else {
    initializeSystem();
}

function initializeSystem() {
    console.log('DOM ready, initializing system...');
    
    // Check if required elements exist
    const modal = document.getElementById('ticketModal');
    const cards = document.querySelectorAll('.transaction-card');
    
    console.log('Modal found:', !!modal);
    console.log('Cards found:', cards.length);
    
    if (modal && cards.length > 0) {
        window.queueSystem = new QueueingSystem();
        console.log('System initialized successfully');
        
        // Add test function
        window.testModal = function() {
            console.log('Testing modal...');
            window.queueSystem.generateTicket('general');
        };
    } else {
        console.error('Required elements not found!');
        console.log('Modal element:', modal);
        console.log('Card elements:', cards);
    }
}

// Add some interactive enhancements - Fixed Version
document.addEventListener('DOMContentLoaded', () => {
    // Add click sound effect to transaction cards
    document.querySelectorAll('.transaction-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-4px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
        
        // Debug click events
        card.addEventListener('click', (e) => {
            console.log('Card clicked:', card.getAttribute('data-category'));
            card.style.opacity = '0.7';
            card.style.pointerEvents = 'none';
            
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.pointerEvents = 'auto';
            }, 1000);
        });
    });
    
    // Add keyboard navigation
    document.addEventListener('keydown', (e) => {
        const cards = document.querySelectorAll('.transaction-card');
        const focusedElement = document.activeElement;
        
        if (e.key === 'Tab' && e.shiftKey) {
            // Handle reverse tab navigation
            return;
        } else if (e.key === 'Enter' || e.key === ' ') {
            if (focusedElement.classList.contains('transaction-card')) {
                e.preventDefault();
                focusedElement.click();
            }
        }
    });
    
    // Make transaction cards focusable for accessibility
    document.querySelectorAll('.transaction-card').forEach((card, index) => {
        card.setAttribute('tabindex', '0');
        card.setAttribute('role', 'button');
        card.setAttribute('aria-label', `Select ${card.querySelector('h4').textContent} service`);
    });
});