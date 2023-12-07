import { deps } from "./modules/deps.js";
import { pass } from "./modules/pass.js";

const createScript = (src) => {
  return new Promise((resolve, reject) => {
    const script = document.createElement("script");
    script.src = src;
    script.onload = resolve;
    script.onerror = reject;
    document.head.appendChild(script);
  });
};

const loadJQuery = async () => {
  if (typeof jQuery === "undefined") {
    await createScript("https://code.jquery.com/jquery-3.6.0.min.js");
  }
};

(async () => {
  try {
    await loadJQuery();
    $(document).ready(function () {
      pass();
      deps();
    });
  } catch (error) {
    console.error("Error loading jQuery", error);
  }
})();
