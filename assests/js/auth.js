document.addEventListener("DOMContentLoaded", () => {
    const eyeToggleButtons = document.querySelectorAll(".eye-toggle-btn");

    eyeToggleButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const inputWrapper = this.closest(".input-box");
            if (!inputWrapper) return;

            const passwordInput = inputWrapper.querySelector("input");
            const icon = this.querySelector("i");

            if (passwordInput && icon) {
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                    icon.classList.remove("ph-eye");
                    icon.classList.add("ph-eye-slash");
                } else {
                    passwordInput.type = "password";
                    icon.classList.remove("ph-eye-slash");
                    icon.classList.add("ph-eye");
                }
            }
        });
    });

    const authForms = document.querySelectorAll("form.ajax-form");

    authForms.forEach((form) => {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]') || this.querySelector("button");
            const originalBtnHtml = submitBtn ? submitBtn.innerHTML : "";

            this.querySelectorAll(".error-text").forEach((el) => {
                el.innerHTML = "";
            });
            this.querySelectorAll(".general-alert-box").forEach((el) => {
                el.innerHTML = "";
            });
            this.querySelectorAll(".input-error").forEach((el) => {
                el.classList.remove("input-error");
            });

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Processing';
            }

            try {
                const formData = new FormData(this);

                const response = await fetch(this.action, {
                    method: this.method || "POST",
                    body: formData,
                    headers: {
                        Accept: "application/json"
                    }
                });

                const data = await response.json().catch(() => ({
                    status: "error",
                    message: "Unexpected server response. Please try again",
                    field: "general"
                }));

                if (data.status === "success") {
                    if (submitBtn) {
                        submitBtn.innerHTML = '<i class="ph-bold ph-check-circle"></i> Success';
                        submitBtn.style.backgroundColor = "var(--neo-teal)";
                    }
                    setTimeout(() => {
                        if (data.redirect) {
                            window.location.href = data.redirect;
                        } else {
                            window.location.reload();
                        }
                    }, 400);
                } else {
                    const field = data.field || "general";
                    const errorEl = this.querySelector(`#${field}-error`) || this.querySelector(`.error-${field}`);

                    if (errorEl) {
                        errorEl.innerHTML = `<i class="ph-bold ph-warning-circle"></i> ${data.message}`;
                    } else {
                        let generalAlert = this.querySelector("#general-error") || this.querySelector(".general-alert-box");
                        if (!generalAlert) {
                            generalAlert = document.createElement("div");
                            generalAlert.className = "general-alert-box";
                            generalAlert.id = "general-error";
                            this.insertBefore(generalAlert, this.firstChild);
                        }
                        generalAlert.innerHTML = `<i class="ph-bold ph-warning-circle"></i> ${data.message}`;
                    }

                    const targetInput = this.querySelector(`[name="${field}"]`) || this.querySelector(`#${field}`);
                    if (targetInput) {
                        targetInput.classList.add("input-error");
                        targetInput.focus();
                    }
                }
            } catch (err) {
                console.error("AJAX Error:", err);
                let generalAlert = this.querySelector("#general-error") || this.querySelector(".general-alert-box");
                if (!generalAlert) {
                    generalAlert = document.createElement("div");
                    generalAlert.className = "general-alert-box";
                    generalAlert.id = "general-error";
                    this.insertBefore(generalAlert, this.firstChild);
                }
                generalAlert.innerHTML = `<i class="ph-bold ph-warning-circle"></i> Connection Failed. Please try again`;
            } finally {
                if (submitBtn) {
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnHtml;
                        submitBtn.style.backgroundColor = "";
                    }, 500);
                }
            }
        });
    });

    const fileInput = document.getElementById("picture");
    const fileChosen = document.getElementById("file-chosen");

    if (fileInput && fileChosen) {
        fileInput.addEventListener("change", function () {
            if (this.files && this.files.length > 0) {
                fileChosen.textContent = this.files[0].name;
            } else {
                fileChosen.textContent = "No file selected";
            }
        });
    }

    const otpBoxes = document.querySelectorAll(".otp-box, .otp-digit");
    const hiddenOtpInput = document.getElementById("otp-value");

    if (otpBoxes.length === 6) {
        const syncHiddenOtp = () => {
            if (hiddenOtpInput) {
                let code = "";
                otpBoxes.forEach((box) => {
                    code += box.value;
                });
                hiddenOtpInput.value = code;
            }
        };

        otpBoxes.forEach((box, index) => {
            box.addEventListener("input", (e) => {
                const val = e.target.value.replace(/[^0-9]/g, "");
                box.value = val ? val.slice(-1) : "";

                if (box.value && index < 5) {
                    otpBoxes[index + 1].focus();
                }
                syncHiddenOtp();
            });

            box.addEventListener("keydown", (e) => {
                if (e.key === "Backspace" && !box.value && index > 0) {
                    otpBoxes[index - 1].focus();
                }
            });

            box.addEventListener("paste", (e) => {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData("text").trim();
                const digits = pasteData.replace(/[^0-9]/g, "").slice(0, 6);

                digits.split("").forEach((digit, i) => {
                    if (otpBoxes[i]) {
                        otpBoxes[i].value = digit;
                    }
                });

                if (digits.length > 0) {
                    const nextFocus = Math.min(digits.length, 5);
                    otpBoxes[nextFocus].focus();
                }
                syncHiddenOtp();
            });
        });
    }
});
