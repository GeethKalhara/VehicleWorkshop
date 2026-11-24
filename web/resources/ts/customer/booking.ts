const form = document.querySelector<HTMLFormElement>('#appointment-booking-form');
const calendarGrid = document.querySelector<HTMLDivElement>('.booking-calendar-grid');
const monthLabel = document.querySelector<HTMLDivElement>('.calendar-month-label');
const dateInput = document.querySelector<HTMLInputElement>('#appointment-date');
const selectedDateLabel = document.querySelector<HTMLParagraphElement>('#booking-selected-date');
const previousButton = document.querySelector<HTMLButtonElement>('[data-calendar-prev]');
const nextButton = document.querySelector<HTMLButtonElement>('[data-calendar-next]');
const feedback = document.querySelector<HTMLParagraphElement>('#booking-feedback');
const reviewDialog = document.querySelector<HTMLDialogElement>('#booking-review-dialog');
const reviewCloseButton = document.querySelector<HTMLButtonElement>('#booking-review-close');
const reviewEditButton = document.querySelector<HTMLButtonElement>('#booking-review-edit');
const reviewConfirmButton = document.querySelector<HTMLButtonElement>('#booking-review-confirm');
const reviewVehicle = document.querySelector<HTMLElement>('#booking-review-vehicle');
const reviewDate = document.querySelector<HTMLElement>('#booking-review-date');
const reviewDescription = document.querySelector<HTMLElement>('#booking-review-description');
const reviewTitle = document.querySelector<HTMLHeadingElement>('#booking-review-title');
const reviewEyebrow = document.querySelector<HTMLElement>('.booking-review-eyebrow');
const reviewSummary = document.querySelector<HTMLDListElement>('.booking-review-summary');
const reviewActions = document.querySelector<HTMLDivElement>('.booking-review-actions');
const confirmingState = document.querySelector<HTMLDivElement>('#booking-confirming-state');

if (
   form
   && calendarGrid
   && monthLabel
   && dateInput
   && selectedDateLabel
   && previousButton
   && nextButton
   && feedback
   && reviewDialog
   && reviewCloseButton
   && reviewEditButton
   && reviewConfirmButton
   && reviewVehicle
   && reviewDate
   && reviewDescription
   && reviewTitle
   && reviewEyebrow
   && reviewSummary
   && reviewActions
   && confirmingState
) {
   const today = new Date();
   today.setHours(0, 0, 0, 0);

   let displayedMonth = new Date(today.getFullYear(), today.getMonth(), 1);
   let selectedDate = '';

   const asDateValue = (date: Date): string => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
   };

   const updateSelection = (): void => {
      dateInput.value = selectedDate;
      if (!selectedDate) {
         selectedDateLabel.textContent = 'No date selected';
         return;
      }

      const year = Number(selectedDate.slice(0, 4));
      const month = Number(selectedDate.slice(5, 7));
      const day = Number(selectedDate.slice(8, 10));
      selectedDateLabel.textContent = `Selected date: ${new Date(year, month - 1, day).toLocaleDateString(undefined, {
         weekday: 'long',
         year: 'numeric',
         month: 'long',
         day: 'numeric',
      })}`;
   };

   const renderCalendar = (): void => {
      monthLabel.textContent = displayedMonth.toLocaleDateString(undefined, {
         month: 'long',
         year: 'numeric',
      });
      previousButton.disabled = displayedMonth.getFullYear() === today.getFullYear()
         && displayedMonth.getMonth() === today.getMonth();

      calendarGrid.replaceChildren();

      const year = displayedMonth.getFullYear();
      const month = displayedMonth.getMonth();
      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const firstWeekday = (new Date(year, month, 1).getDay() + 6) % 7;

      for (let index = 0; index < firstWeekday; index += 1) {
         const spacer = document.createElement('span');
         spacer.className = 'calendar-day calendar-day-outside';
         spacer.setAttribute('aria-hidden', 'true');
         calendarGrid.append(spacer);
      }

      for (let day = 1; day <= daysInMonth; day += 1) {
         const date = new Date(year, month, day);
         const value = asDateValue(date);
         const button = document.createElement('button');
         button.type = 'button';
         button.className = 'calendar-day';
         button.textContent = String(day);
         button.dataset.date = value;
         button.disabled = date < today;
         button.setAttribute('aria-label', date.toLocaleDateString(undefined, {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
         }));

         if (value === selectedDate) {
            button.classList.add('calendar-day-selected');
            button.setAttribute('aria-pressed', 'true');
         } else {
            button.setAttribute('aria-pressed', 'false');
         }

         calendarGrid.append(button);
      }
   };

   calendarGrid.addEventListener('click', (event: MouseEvent) => {
      const target = event.target;
      if (!(target instanceof HTMLButtonElement) || !target.dataset.date) return;

      selectedDate = target.dataset.date;
      updateSelection();
      renderCalendar();
      feedback.textContent = '';
   });

   previousButton.addEventListener('click', () => {
      displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() - 1, 1);
      renderCalendar();
   });

   nextButton.addEventListener('click', () => {
      displayedMonth = new Date(displayedMonth.getFullYear(), displayedMonth.getMonth() + 1, 1);
      renderCalendar();
   });

   form.addEventListener('submit', (event: SubmitEvent) => {
      event.preventDefault();
      feedback.textContent = '';

      if (!form.reportValidity()) return;
      if (!selectedDate) {
         feedback.textContent = 'Choose an appointment date to continue.';
         return;
      }

      const vehicle = form.querySelector<HTMLSelectElement>('#booking-vehicle');
      const description = form.querySelector<HTMLTextAreaElement>('#booking-description');
      if (!vehicle || !description) return;

      const selectedVehicle = vehicle.options[vehicle.selectedIndex]?.textContent?.trim() ?? '';
      const selectedDateText = selectedDateLabel.textContent?.replace('Selected date: ', '') ?? '';

      reviewVehicle.textContent = selectedVehicle;
      reviewDate.textContent = selectedDateText;
      reviewDescription.textContent = description.value.trim();
      reviewDialog.showModal();
   });

   const closeReview = (): void => {
      if (reviewDialog.open) {
         reviewDialog.close();
      }
   };

   reviewCloseButton.addEventListener('click', closeReview);
   reviewEditButton.addEventListener('click', closeReview);

   reviewConfirmButton.addEventListener('click', () => {
      sessionStorage.setItem('confirmedBooking', JSON.stringify({
         vehicle: reviewVehicle.textContent,
         date: reviewDate.textContent,
         description: reviewDescription.textContent,
      }));

      reviewDialog.classList.add('is-confirming');
      reviewDialog.setAttribute('aria-busy', 'true');
      reviewTitle.textContent = 'Confirming your booking...';
      reviewEyebrow.hidden = true;
      reviewCloseButton.hidden = true;
      reviewSummary.hidden = true;
      reviewActions.hidden = true;
      confirmingState.hidden = false;

      window.setTimeout(() => {
         window.location.assign('/customer/appointments?booking=confirmed');
      }, 1800);
   });

   reviewDialog.addEventListener('click', (event: MouseEvent) => {
      if (event.target === reviewDialog) {
         closeReview();
      }
   });

   renderCalendar();
   updateSelection();
}
