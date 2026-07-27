import { initMRZReader, parseMrz, isMRZ, extractMRZData } from 'web-mrz-reader';
import Tesseract from 'tesseract.js';

window.MRZReader = { initMRZReader, parseMrz, isMRZ, extractMRZData };
window.Tesseract = Tesseract;
