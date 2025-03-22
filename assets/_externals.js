import { Modal } from "bootstrap"; // single Import für Modal

document.addEventListener("DOMContentLoaded", function () {
  let myModal = document.getElementById("successModal");
  let modal = new Modal(myModal);
  modal.show();
});
