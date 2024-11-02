// js/overlay.js
// HAPUS SEMUA SCRIPT INI untuk menghilangkan hover overlay effect
const hoverOverlay = document.getElementById("hoverOverlay");
let throttleTimeout;

document
  .querySelector(".hero-section")
  .addEventListener("mousemove", function (event) {
    if (!throttleTimeout) {
      const rect = this.getBoundingClientRect();
      const x = event.clientX - rect.left;
      const y = event.clientY - rect.top;

      hoverOverlay.style.clipPath = `circle(150px at ${x}px ${y}px)`;

      throttleTimeout = setTimeout(() => {
        throttleTimeout = null;
      }, 20);
    }
  });

document
  .querySelector(".hero-section")
  .addEventListener("mouseleave", function () {
    hoverOverlay.style.clipPath = "circle(0px at 50% 50%)";
  });
