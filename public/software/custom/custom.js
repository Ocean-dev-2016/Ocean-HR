// Function to get CSRF token
function getCsrfToken() {
    let token = $('meta[name="csrf-token"]').attr('content');
    if (!token) {
        // Fallback: get token from Laravel's csrf_token() if meta tag doesn't exist
        token = '{{ csrf_token() }}';
    }
    return token;
}

function isNumber(evt) {
    evt = evt ? evt : window.event;
    var charCode = evt.which ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57)) {
        return false;
    }
    return true;
}

function isAlphanumeric(evt) {
    evt = evt || window.event;
    var charCode = evt.which || evt.keyCode;
    var charStr = String.fromCharCode(charCode);

    if (!charStr.match(/^[a-zA-Z0-9 ]$/)) {
        evt.preventDefault();
        return false;
    }
    return true;
}

function isDecimal(evt) {
    evt = evt || window.event;
    var charCode = evt.which || evt.keyCode;

    if ([8, 46, 37, 39].includes(charCode)) {
        return true;
    }

    if (charCode === 46) {
        if (evt.target.value.includes(".")) {
            return false;
        }
        return true;
    }

    if (charCode >= 48 && charCode <= 57) {
        return true;
    }
    return false;
}

function formatDateTime(date) {
    const pad = (n) => (n < 10 ? "0" + n : n);

    const year = date.getFullYear();
    const month = pad(date.getMonth() + 1); // Months are zero-based
    const day = pad(date.getDate());

    const hours = pad(date.getHours());
    const minutes = pad(date.getMinutes());
    const seconds = pad(date.getSeconds());

    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
}

function formatToDMYAMPM(input) {
    const dateObj =
        input instanceof Date ? input : new Date(input.replace(/-/g, "/"));

    const day = String(dateObj.getDate()).padStart(2, "0");
    const month = String(dateObj.getMonth() + 1).padStart(2, "0");
    const year = dateObj.getFullYear();

    let hours = dateObj.getHours();
    const minutes = String(dateObj.getMinutes()).padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";

    hours = hours % 12;
    hours = hours ? hours : 12;

    return `${day}-${month}-${year} ${String(hours).padStart(
        2,
        "0"
    )}:${minutes} ${ampm}`;
}

const ckeditorInstances = {}; // Global object to hold instances

function initializeCKEditor(
    selector,
    basicItems = [],
    additionalConfig = {},
    editorHeight = "180px"
) {
    const defaultToolbar = {
        items:
            basicItems.length > 0
                ? basicItems
                : [
                    "undo",
                    "redo",
                    "|",
                    "heading",
                    "|",
                    "bold",
                    "italic",
                    "|",
                    "link",
                    "insertImage",
                    "insertTable",
                    "blockQuote",
                    "mediaEmbed",
                    "|",
                    "bulletedList",
                    "numberedList",
                    "|",
                    "alignment",
                    "|",
                    "sourceEditing",
                ],
        shouldNotGroupWhenFull: true,
    };

    const defaultConfig = {
        toolbar: defaultToolbar,
        placeholder: "Start typing...",
        heading: {
            options: [
                {
                    model: "paragraph",
                    title: "Paragraph",
                    class: "ck-heading_paragraph",
                },
                {
                    model: "heading1",
                    view: "h1",
                    title: "Heading 1",
                    class: "ck-heading_heading1",
                },
                {
                    model: "heading2",
                    view: "h2",
                    title: "Heading 2",
                    class: "ck-heading_heading2",
                },
            ],
        },
        removePlugins: [
            "CKBox",
            "CKFinder",
            "EasyImage",
            "RealTimeCollaborativeComments",
            "RealTimeCollaborativeTrackChanges",
            "RealTimeCollaborativeRevisionHistory",
            "PresenceList",
            "Comments",
            "TrackChanges",
            "TrackChangesData",
            "RevisionHistory",
            "Pagination",
            "WProofreader",
            "MathType",
        ],
    };

    const finalConfig = Object.assign({}, defaultConfig, additionalConfig);

    document.querySelectorAll(selector).forEach((element, index) => {
        CKEDITOR.ClassicEditor.create(element, finalConfig)
            .then((editor) => {
                editor.editing.view.change((writer) => {
                    writer.setStyle(
                        "min-height",
                        editorHeight,
                        editor.editing.view.document.getRoot()
                    );
                });

                // Save instance by element ID
                const elementId = element.id || `ckeditor-${index}`;
                ckeditorInstances[elementId] = editor;

                console.log(`Editor ${elementId} initialized`, editor);
            })
            .catch((error) => {
                console.error(`Error initializing editor:`, error);
            });
    });
}

if (
    $('meta[name="currentGuard"]').attr("value") == "employee" &&
    $('meta[name="parent_type_id"]').attr("value") != ""
) {
    function detectBrowserFromUA() {
        const userAgent = navigator.userAgent;

        if (
            userAgent.includes("Chrome") &&
            !userAgent.includes("Edg") &&
            !userAgent.includes("OPR")
        ) {
            return "chrome";
        } else if (
            userAgent.includes("Safari") &&
            !userAgent.includes("Chrome")
        ) {
            return "safari";
        } else if (userAgent.includes("Firefox")) {
            return "firefox";
        } else if (
            userAgent.includes("MSIE") ||
            userAgent.includes("Trident")
        ) {
            return "ie";
        } else if (userAgent.includes("Edg")) {
            return "msedge";
        } else if (userAgent.includes("OPR") || userAgent.includes("Opera")) {
            return "opera";
        } else {
            return "unknown";
        }
    }

    function getBrowserDetails() {
        var navigatorDetails = {};
        for (var key in navigator) {
            try {
                var value = navigator[key];
                if (typeof value !== "function" && typeof value !== "object") {
                    navigatorDetails[key] = value;
                }
            } catch (e) {
                navigatorDetails[key] = "Access denied or unavailable";
            }
        }

        navigatorDetails.detected_browser = detectBrowserFromUA();
        return navigatorDetails;
    }

    // Function to get geolocation
    // function getGeolocation(callback) {
    //     if (navigator.geolocation) {
    //         navigator.geolocation.getCurrentPosition(
    //             function (position) {
    //                 const locationDetails = {
    //                     latitude: position.coords.latitude,
    //                     longitude: position.coords.longitude,
    //                     accuracy: position.coords.accuracy,
    //                     timestamp: new Date(position.timestamp).toLocaleString()
    //                 };
    //                 callback(null, locationDetails); // Success
    //             },
    //             function (error) {
    //                 callback(error.message, null); // Error
    //             }
    //         );
    //     } else {
    //         callback("Geolocation is not supported by this browser.", null);
    //     }
    // }

    function getGeolocation(callback) {
        if (navigator.geolocation) {
            navigator.permissions
                .query({ name: "geolocation" })
                .then(function (result) {
                    if (
                        result.state === "granted" ||
                        result.state === "prompt"
                    ) {
                        navigator.geolocation.getCurrentPosition(
                            function (position) {
                                const locationDetails = {
                                    latitude: position.coords.latitude,
                                    longitude: position.coords.longitude,
                                    accuracy: position.coords.accuracy,
                                    timestamp: new Date(
                                        position.timestamp
                                    ).toLocaleString(),
                                };
                                callback(null, locationDetails); // Success
                            },
                            function (error) {
                                callback(error.message, null); // Error
                            }
                        );
                    } else {
                        // Permission denied earlier
                        callback("Permission denied", null);
                    }
                });
        } else {
            callback("Geolocation is not supported by this browser.", null);
        }
    }

    // Example usage
    function getGeolocationcall() {
        const browserDetails = getBrowserDetails();
        $("#navigator_browser_info").attr(
            "data-navigator-browser-info",
            JSON.stringify(browserDetails, null, 2)
        );

        getGeolocation(function (error, locationDetails) {
            if (error) {
                $(".buy-now").hide();
                //$('#navigator_location_info').attr("data-navigator-location-info",error);
                // if (typeof punchinout_url !== 'undefined' && punchinout_url) {
                //     window.location.href = punchinout_url;
                // }
            } else {
                $("#navigator_location_info").attr(
                    "data-navigator-location-info",
                    JSON.stringify(locationDetails, null, 2)
                );
                $(".buy-now").show();
                // if (window.location.pathname === "/software/permissions") {
                //     var locationData = $('#navigator_location_info').attr("data-navigator-location-info");
                //     var parsedLocation = locationData ? JSON.parse(locationData) : {};

                //     if (parsedLocation.latitude && parsedLocation.longitude) {
                //         window.location.href = dashboard_url;
                //     }
                // }
                // $('body').css({
                //     'opacity': '',
                //     'pointer-events': ''
                // });
            }
        });
    }
    getGeolocationcall();
    // let details = getBrowserDetails();
}

$(".sameAsParent").on("click change", function () {
    var parentId = $(this).attr("data-parent-id");
    var childId = $(this).attr("data-child-id");
    var $parent = $("#" + parentId);
    var $child = $("#" + childId);

    if ($(this).is(":checked")) {
        $child.val($parent.val());
    } else {
        $child.val("");
    }

    // also update child when parent changes, if checkbox is checked
    $parent.on("keyup change", function () {
        if (
            $(".sameAsParent[data-parent-id='" + parentId + "']").is(":checked")
        ) {
            $child.val($(this).val());
        }
    });
});

$(document).ready(function () {
    // Accept Number only
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".number_only").forEach(function (el) {
            let optionNumberOnly = {
                numericOnly: true,
            };

            if (el) {
                // store old value before cleave init
                let oldVal = el.value;

                // init cleave
                let cleave = new Cleave(el, optionNumberOnly);

                // restore old value so it shows correctly
                if (oldVal) {
                    cleave.setRawValue(oldVal);
                }
            }

            // Validation function
            function validateInput() {
                let value = el.value.trim();

                // Ensure numeric
                if (!/^\d+$/.test(value)) {
                    showError(el, "Only numbers are allowed");
                    return false;
                }

                let numValue = parseInt(value, 10);

                // Min/Max validation (from HTML attributes)
                let min = el.hasAttribute("min")
                    ? parseInt(el.getAttribute("min"), 10)
                    : null;
                let max = el.hasAttribute("max")
                    ? parseInt(el.getAttribute("max"), 10)
                    : null;

                if (min !== null && numValue < min) {
                    showError(el, `Value must be at least ${min}`);
                    return false;
                }

                if (max !== null && numValue > max) {
                    showError(el, `Value must not exceed ${max}`);
                    return false;
                }

                clearError(el);
                return true;
            }

            // Attach validation on blur & input
            // el.addEventListener("blur", validateInput);
            // el.addEventListener("input", validateInput);
        });
    });

    // Helpers to show/hide error messages
    function showError(el, message) {
        el.classList.add("is-invalid");

        let errorEl = el.nextElementSibling;
        if (!errorEl || !errorEl.classList.contains("invalid-feedback")) {
            errorEl = document.createElement("div");
            errorEl.className = "invalid-feedback";
            el.insertAdjacentElement("afterend", errorEl);
        }
        errorEl.textContent = message;
    }

    function clearError(el) {
        el.classList.remove("is-invalid");

        let errorEl = el.nextElementSibling;
        if (errorEl && errorEl.classList.contains("invalid-feedback")) {
            errorEl.textContent = "";
        }
    }

    // Aadhaar card mask: XXXX XXXX XXXX
    document.querySelectorAll("#aadhar_card_number").forEach(function (el) {
        new Cleave(el, {
            blocks: [4, 4, 4],
            numericOnly: true,
        });
    });

    // PAN card mask: ABCDE1234F
    document.querySelectorAll("#pan_card_number").forEach(function (el) {
        new Cleave(el, {
            blocks: [5, 4, 1],
            uppercase: true,
            alphanumericOnly: true,
        });
    });

    // Global AJAX Error Handler for Session Expiry (401 Unauthorized)
    $(document).ajaxError(function (event, jqXHR, ajaxSettings, thrownError) {
        if (jqXHR.status === 401) {
            // Check if it's a genuine session expiry rather than a validation/custom application error
            let isSessionExpired = true;
            if (jqXHR.responseJSON && jqXHR.responseJSON.status === false) {
                isSessionExpired = false;
            }
            if (isSessionExpired) {
                // If not on the login page, reload to trigger redirection
                if (!window.location.pathname.includes('/login')) {
                    window.location.reload();
                }
            }
        }
    });
});

// Prevent mouse wheel scrolling from changing number input values globally
document.addEventListener('wheel', function (e) {
    if (e.target.tagName === 'INPUT' && e.target.type === 'number') {
        e.preventDefault();
        e.target.blur();
    }
}, { passive: false });
