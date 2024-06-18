// Stimulus
import "./bootstrap";

// Bootstrap
import "bootstrap";

// Styles
import "./styles/styles.scss";

// Scripts
import "./scripts/scripts.js";

// Import all images from assets/images (recursive) (keeps folder structure)
function importAll(r) {
    r.keys().forEach(r);
}

importAll(require.context("./images", true, /\.(png|jpe?g|svg)$/));
