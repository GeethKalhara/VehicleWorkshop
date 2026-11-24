/**
 * Customer Vehicles Page TypeScript
 */

const vehiclesGrid = document.querySelector<HTMLElement>('.vehicles-grid');
const detailsDialog = document.querySelector<HTMLDialogElement>('#vehicle-details-dialog');
const detailsCloseButton = document.querySelector<HTMLButtonElement>('.vehicle-details-close');
const detailsTitle = document.querySelector<HTMLHeadingElement>('#vehicle-details-title');
const detailsModel = document.querySelector<HTMLElement>('#vehicle-details-model');
const detailsRegistration = document.querySelector<HTMLElement>('#vehicle-details-registration');
const detailsMileage = document.querySelector<HTMLElement>('#vehicle-details-mileage');
const detailsVin = document.querySelector<HTMLElement>('#vehicle-details-vin');

if (
   vehiclesGrid
   && detailsDialog
   && detailsCloseButton
   && detailsTitle
   && detailsModel
   && detailsRegistration
   && detailsMileage
   && detailsVin
) {
   vehiclesGrid.addEventListener('click', (event: MouseEvent) => {
      const target = event.target;
      if (!(target instanceof Element)) return;

      const detailsButton = target.closest<HTMLButtonElement>('[data-view-vehicle-details]');
      if (!detailsButton) return;

      const vehicleCard = detailsButton.closest<HTMLElement>('.vehicle-card');
      const options = detailsButton.closest<HTMLDetailsElement>('.vehicle-options');
      if (!vehicleCard) return;

      detailsTitle.textContent = vehicleCard.querySelector('.vehicle-card-title')?.textContent?.trim() ?? '';
      detailsModel.textContent = detailsTitle.textContent;
      detailsRegistration.textContent = vehicleCard.querySelector('.license-plate-badge')?.textContent?.trim() ?? '';
      detailsMileage.textContent = vehicleCard.dataset.mileage ?? '';
      detailsVin.textContent = vehicleCard.dataset.vin ?? '';

      if (options) {
         options.open = false;
      }

      detailsDialog.showModal();
   });

   detailsCloseButton.addEventListener('click', () => {
      detailsDialog.close();
   });

   detailsDialog.addEventListener('click', (event: MouseEvent) => {
      if (event.target === detailsDialog) {
         detailsDialog.close();
      }
   });
}

