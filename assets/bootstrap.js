// assets/bootstrap.js
import { startStimulusApp } from "@symfony/stimulus-bridge";
import "bootstrap/dist/css/bootstrap.min.css";
import "bootstrap";

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(
  require.context(
    "@symfony/stimulus-bridge/lazy-controller-loader!./controllers",
    true,
    /\.(j|t)sx?$/
  )
);
