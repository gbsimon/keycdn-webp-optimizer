/**
 * KeyCDN WebP Optimizer - Admin JavaScript
 */

jQuery(document).ready(function ($) {
	"use strict"

	// Quality input validation
	$("#keycdn_webp_quality").on("input", function () {
		var value = parseInt($(this).val())
		var min = parseInt($(this).attr("min"))
		var max = parseInt($(this).attr("max"))

		if (value < min) {
			$(this).val(min)
		} else if (value > max) {
			$(this).val(max)
		}
	})

	// Enhanced mode toggle effect
	$("#keycdn_webp_enhanced").on("change", function () {
		var isChecked = $(this).is(":checked")
		var description = $(this).closest("td").find(".description")

		if (isChecked) {
			description.addClass("enhanced-enabled")
		} else {
			description.removeClass("enhanced-enabled")
		}
	})

	// Debug mode warning
	$("#keycdn_webp_debug").on("change", function () {
		var isChecked = $(this).is(":checked")

		if (isChecked) {
			if (!confirm("Debug mode will add HTML comments to your pages. This should only be enabled for troubleshooting. Continue?")) {
				$(this).prop("checked", false)
			}
		}
	})

	// Form validation before submit
	$("form").on("submit", function (e) {
		var qualityInput = $("#keycdn_webp_quality")
		var qualityValue = qualityInput.val()
		var quality = parseInt(qualityValue)

		// Check if input is empty or invalid
		if (qualityValue === "" || isNaN(quality)) {
			e.preventDefault()
			alert("Please enter a valid quality value between 1 and 100.")
			qualityInput.focus()
			return false
		}

		// Check if value is within range
		if (quality < 1 || quality > 100) {
			e.preventDefault()
			alert("Please enter a valid quality value between 1 and 100.")
			qualityInput.focus()
			return false
		}
	})

	// Status indicator animation
	function animateStatusIndicator() {
		$(".status-indicator").each(function () {
			$(this).addClass("animate")
			setTimeout(
				function () {
					$(this).removeClass("animate")
				}.bind(this),
				1000
			)
		})
	}

	// Animate status indicators on page load
	setTimeout(animateStatusIndicator, 500)

	// Add smooth transitions
	$(".status-indicator").css({
		transition: "all 0.3s ease-in-out",
	})

	// Enhanced mode description styling
	$("<style>")
		.prop("type", "text/css")
		.html(
			`
            .enhanced-enabled {
                color: #0073aa !important;
                font-weight: 600;
            }
            .status-indicator.animate {
                transform: scale(1.1);
                box-shadow: 0 0 10px rgba(0, 115, 170, 0.3);
            }
        `
		)
		.appendTo("head")
})
