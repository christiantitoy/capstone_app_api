const modal = document.getElementById("appModal");
const modalTitle = document.getElementById("modalTitle");
const modalMessage = document.getElementById("modalMessage");
const closeBtn = document.querySelector(".close-btn");

/* Reusable modal function */
function showModal(type, message){

    modal.classList.remove("modal-error","modal-success","modal-warning");

    if(type === "error"){
        modalTitle.textContent = "Error";
        modal.classList.add("modal-error");
    }

    if(type === "success"){
        modalTitle.textContent = "Success";
        modal.classList.add("modal-success");
    }

    if(type === "warning"){
        modalTitle.textContent = "Warning";
        modal.classList.add("modal-warning");
    }

    modalMessage.textContent = message;
    modal.style.display = "block";
}

/* Close modal */
closeBtn.onclick = () => modal.style.display = "none";

window.onclick = (e)=>{
    if(e.target === modal){
        modal.style.display = "none";
    }
};

/* Show PHP messages */
if(phpError){
    showModal("error", phpError);
}

if(phpSuccess){
    showModal("success", phpSuccess);
}