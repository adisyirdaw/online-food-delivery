
document.addEventListener("DOMContentLoaded", function () {

    const modal = document.getElementById("complaintModal");
    const openBtn = document.getElementById("openComplaint");
    const closeBtn = document.querySelector(".close");
    const form = document.getElementById("complaintForm");
    const messageBox = document.getElementById("messageBox");

    openBtn.addEventListener("click", function (e) {
        e.preventDefault();
        modal.style.display = "block";
        document.body.classList.add("modal-open"); // ✅ ADD
    });

    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
        document.body.classList.remove("modal-open"); // ✅ ADD
    });

    window.addEventListener("click", function (e) {
        if (e.target === modal) {
            modal.style.display = "none";
            document.body.classList.remove("modal-open"); // ✅ ADD
        }
    });

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        fetch("complain.php", {
            method: "POST",
            body: new FormData(form)
        })
        .then(res => res.text())
        .then(msg => {
            messageBox.innerHTML = msg;
            form.reset();
        });
    });

});
