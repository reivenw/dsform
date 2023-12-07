((d) => {
  const appendElement = (tag, attributes, parent) => {
    const element = d.createElement(tag);
    Object.assign(element, attributes);
    parent.appendChild(element);
  };

  appendElement("link", { rel: "stylesheet", href: "../src/assets/css/dsf.css" }, d.head);
  appendElement("script", { src: "../src/assets/js/dsf.js", type: "module" }, d.body);
})(document);
