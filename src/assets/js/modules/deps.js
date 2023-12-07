export function deps() {
  const isValid = (depsList, target) => {
    return depsList
      .map((objectDep) => ({
        ...objectDep,
        input: $(target)
          .find(`[name='${objectDep.input}']`)
          .filter((_, input) => ([`checkbox`, `radio`].includes(input.type) ? input.checked : input)),
      }))
      .every((objectDep) =>
        objectDep.input.length !== 0
          ? Array.from(objectDep.input)
              .map((input) => $(input).val())
              .every((value) => value === objectDep.value)
          : false
      );
  };

  const display = (fieldDepOn, depsList, target) => {
    $(fieldDepOn)
      [isValid(depsList, target) ? `removeAttr` : `attr`](`data-deps`, ``)
      .closest(`form`)
      .on(`submit`, () => {
        $(fieldDepOn)
          .find(`input , select , textarea`)
          .each((_, input) => {
            if (!isValid(depsList, target)) {
              input.value = ``;
            }
          });
      });
  };

  $(".dsf").each((_, form) =>
    $(form)
      .find(`[data-deps]`)
      .each((_, fieldDepOn) => {
        const depsList = $(fieldDepOn).data("deps");
        $.each(depsList, (i, objectDep) => {
          const handler = (e) => {
            $(e.target).is(`[name='${objectDep.input}']`) ? display(fieldDepOn, depsList, form) : null;
          };
          $(form).on("change", handler);
        });
        display(fieldDepOn, depsList, form);
      })
  );
}
