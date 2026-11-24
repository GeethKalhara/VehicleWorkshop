/**
 * Customer Invoices Page — TypeScript
 *
 * Handles:
 *  - Vehicle dropdown filter
 *  - Date range filter (from / to)
 *  - Combined filter logic (preserves both filters)
 *  - Active filter chips display
 *  - Reset Filters button
 *  - Live visible-row counter update
 */

// --------------------------------------------------------------------------
// Types
// --------------------------------------------------------------------------

interface InvoiceFilters {
   vehicle: string;   // license_plate value, empty = all
   dateFrom: string;  // ISO date string YYYY-MM-DD, empty = no bound
   dateTo: string;    // ISO date string YYYY-MM-DD, empty = no bound
}

// --------------------------------------------------------------------------
// DOM references
// --------------------------------------------------------------------------

const vehicleFilter  = document.querySelector<HTMLSelectElement>('#inv-vehicle-filter');
const dateFromInput  = document.querySelector<HTMLInputElement>('#inv-date-from');
const dateToInput    = document.querySelector<HTMLInputElement>('#inv-date-to');
const resetBtn       = document.querySelector<HTMLButtonElement>('#inv-reset-btn');
const activeFiltersEl = document.querySelector<HTMLElement>('#inv-active-filters');
const visibleCountEl  = document.querySelector<HTMLElement>('#inv-visible-count');
const tbody           = document.querySelector<HTMLTableSectionElement>('#invoices-tbody');

if (!tbody) {
   // Guard: nothing to do if table is absent
} else {
   const rows = Array.from(tbody.querySelectorAll<HTMLTableRowElement>('.invoice-row'));

   // Current filter state
   const filters: InvoiceFilters = { vehicle: '', dateFrom: '', dateTo: '' };

   // --------------------------------------------------------------------------
   // Core filter logic
   // --------------------------------------------------------------------------

   function applyFilters(): void {
      let visibleCount = 0;

      rows.forEach((row) => {
         const rowVehicle = row.dataset.vehicle ?? '';
         const rowDate    = row.dataset.date ?? '';   // ISO format YYYY-MM-DD

         const matchVehicle  = filters.vehicle === '' || rowVehicle === filters.vehicle;
         const matchDateFrom = filters.dateFrom === '' || rowDate >= filters.dateFrom;
         const matchDateTo   = filters.dateTo === ''   || rowDate <= filters.dateTo;

         const visible = matchVehicle && matchDateFrom && matchDateTo;
         row.style.display = visible ? '' : 'none';
         if (visible) visibleCount++;
      });

      // Update counter
      if (visibleCountEl) {
         visibleCountEl.textContent = String(visibleCount);
      }

      renderActiveChips();
   }

   // --------------------------------------------------------------------------
   // Active filter chip rendering
   // --------------------------------------------------------------------------

   function renderActiveChips(): void {
      if (!activeFiltersEl) return;

      const chips: string[] = [];

      if (filters.vehicle !== '') {
         const label = vehicleFilter?.options[vehicleFilter.selectedIndex]?.text ?? filters.vehicle;
         chips.push(buildChip('vehicle', `Vehicle: ${label}`));
      }

      if (filters.dateFrom !== '') {
         chips.push(buildChip('dateFrom', `From: ${formatDate(filters.dateFrom)}`));
      }

      if (filters.dateTo !== '') {
         chips.push(buildChip('dateTo', `To: ${formatDate(filters.dateTo)}`));
      }

      if (chips.length === 0) {
         activeFiltersEl.hidden = true;
         activeFiltersEl.innerHTML = '';
      } else {
         activeFiltersEl.hidden = false;
         activeFiltersEl.innerHTML = chips.join('');
         // Attach remove handlers
         activeFiltersEl.querySelectorAll<HTMLButtonElement>('.inv-filter-chip-remove').forEach((btn) => {
            btn.addEventListener('click', () => {
               const key = btn.dataset.key as keyof InvoiceFilters | undefined;
               if (!key) return;
               clearFilter(key);
            });
         });
      }
   }

   function buildChip(key: string, label: string): string {
      return `<span class="inv-filter-chip">
         ${escapeHtml(label)}
         <button type="button" class="inv-filter-chip-remove" data-key="${escapeHtml(key)}" aria-label="Remove filter">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
               <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
         </button>
      </span>`;
   }

   function clearFilter(key: keyof InvoiceFilters): void {
      filters[key] = '';

      if (key === 'vehicle' && vehicleFilter) {
         vehicleFilter.value = '';
      }
      if (key === 'dateFrom' && dateFromInput) {
         dateFromInput.value = '';
      }
      if (key === 'dateTo' && dateToInput) {
         dateToInput.value = '';
      }

      applyFilters();
   }

   // --------------------------------------------------------------------------
   // Event listeners
   // --------------------------------------------------------------------------

   vehicleFilter?.addEventListener('change', () => {
      filters.vehicle = vehicleFilter.value;
      applyFilters();
   });

   dateFromInput?.addEventListener('change', () => {
      filters.dateFrom = dateFromInput.value;
      // Auto-clamp: dateFrom cannot be after dateTo
      if (filters.dateTo !== '' && filters.dateFrom > filters.dateTo) {
         filters.dateFrom = filters.dateTo;
         dateFromInput.value = filters.dateFrom;
      }
      applyFilters();
   });

   dateToInput?.addEventListener('change', () => {
      filters.dateTo = dateToInput.value;
      // Auto-clamp: dateTo cannot be before dateFrom
      if (filters.dateFrom !== '' && filters.dateTo < filters.dateFrom) {
         filters.dateTo = filters.dateFrom;
         dateToInput.value = filters.dateTo;
      }
      applyFilters();
   });

   resetBtn?.addEventListener('click', () => {
      filters.vehicle  = '';
      filters.dateFrom = '';
      filters.dateTo   = '';

      if (vehicleFilter)  vehicleFilter.value  = '';
      if (dateFromInput)  dateFromInput.value  = '';
      if (dateToInput)    dateToInput.value    = '';

      applyFilters();
   });
}

// --------------------------------------------------------------------------
// Utility helpers
// --------------------------------------------------------------------------

function escapeHtml(str: string): string {
   return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
}

function formatDate(iso: string): string {
   if (!iso) return '';
   const d = new Date(iso + 'T00:00:00');
   if (isNaN(d.getTime())) return iso;
   return d.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

export {};
