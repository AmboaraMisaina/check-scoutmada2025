import './stimulus_bootstrap.js';
// assets/app.js
import { startStimulusApp } from '@symfony/stimulus-bridge';

// Démarre Stimulus et charge automatiquement TOUS les controllers dans assets/controllers/
export const app = startStimulusApp();