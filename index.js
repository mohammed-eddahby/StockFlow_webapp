 function showForm(FormID) {
    document.querySelectorAll(".form-box").forEach(form => form.classList.remove("active"));
    document.getElementById(FormID).classList.add("active");
}