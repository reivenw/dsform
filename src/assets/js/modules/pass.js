export function pass() {
  const alph = `ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz()[]/*?@#$%&^!~\\1234567890`,
    tests = [/^(?=.*\d).{8,}$/, /[a-z]/, /^(?=.*[A-Z])(?=.*[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]+)/],
    strengths = { short: "Very weak", bad: "Weak", good: "Medium", strong: "Strong" };

  const getPass = (l = 16) => [...Array(l)].map(() => alph[Math.floor(Math.random() * alph.length)]).join("");

  const generate = (...input) => {
    input.forEach((e) => {
      const elem = $(e);
      elem.val(getPass()).data("hidden", false);
      if (elem.siblings().find(`[data-action="hider"]`).length > 0) hider(e);
      if (elem.not("[data-confirm]").length > 0) check(e);
    });
    input[0].focus();
  };

  const hider = (input) => {
    const hider = $(input).siblings().find(`[data-action="hider"]`);
    if (hider.length > 0) {
      $(input).attr("type", $(input).data("hidden") ? "password" : "text");
      hider.html(hider.attr($(input).data("hidden") ? "aria-label" : "aria-label-hidden"));
    }
  };

  const check = (input) => {
    const password = $(input).val(),
      msgCheck = $(input).parent().find(`#pass-strength-result`),
      strength = tests.reduce((count, test) => count + test.test(password), 0),
      strengthClass = Object.keys(strengths)[strength];

    if (password === "") {
      msgCheck.removeClass().addClass("msg-hidden");
      $(input).removeClass(Object.keys(strengths).join(" "));
    } else {
      msgCheck.removeClass("msg-hidden").html(Object.values(strengths)[strength]).attr("class", strengthClass);
      $(input).removeClass(Object.keys(strengths).join(" ")).addClass(strengthClass);
    }
  };

  $(".dsf")
    .find("[type='password']:not([data-confirm])")
    .each(function () {
      const input = $(this);
      const controls = input.siblings(".pass-controls");

      if (controls.find(`[data-action="enabler"]`).length > 0) {
        $(this).attr("data-enabled", `false`);
      }

      if (controls.length > 0) {
        controls.on("click", function (e) {
          e.preventDefault();
          const target = $(e.target);

          if (target.is('[data-action="hider"], [data-action="hider"] *')) {
            const hidden = !input.data("hidden");
            input.data("hidden", hidden);
            hider(input);
          }

          if (target.is('[data-action="generator"], [data-action="generator"] *')) {
            generate(input);
          }

          input
            .parent()
            .find("[data-enabled]")
            .each(function () {
              if (target.is('[data-action="enabler"], [data-action="enabler"] *')) {
                $(this).attr("data-enabled", true);
                generate(input);
              } else if (target.is('[data-action="canceler"], [data-action="canceler"] *')) {
                $(this).attr("data-enabled", false);
                input.val("");
              }
            });
        });

        input.on("input", () => check(input));

        generate(input);
        check(input);
      }
    });
}
