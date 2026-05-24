import { init as initFilters } from './filters.js';
import { init as initAvailability } from './availability.js';
import { init as initOrder } from './order.js';

document.addEventListener('DOMContentLoaded', () => {
    initFilters();
    initAvailability();
    initOrder();
});