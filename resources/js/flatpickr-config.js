import flatpickr from "flatpickr";

// Initialize Check-in Date Picker
flatpickr("#check_in", {
    minDate: "today",
    dateFormat: "Y-m-d",
    disableMobile: true,
    onChange: function(selectedDates) {
        // When check-in is selected, set check-out min date
        if (selectedDates.length > 0) {
            const checkOutPicker = document.querySelector("#check_out")._flatpickr;
            if (checkOutPicker) {
                checkOutPicker.set("minDate", new Date(selectedDates[0].getTime() + 86400000)); // +1 day
            }
        }
    }
});

// Initialize Check-out Date Picker
flatpickr("#check_out", {
    minDate: "today",
    dateFormat: "Y-m-d",
    disableMobile: true
});
