<?php
namespace Vanderbilt\KnowYourNumbers;

class KnowYourNumbers extends \ExternalModules\AbstractExternalModule {
	
	function inKnowYourNumbersSurvey() {
		?>
		<script type='text/javascript'>
			var KnowYourNumbers = {};
			KnowYourNumbers.asianWeightOptions = [
				["<104", "104-142", "143-190", "191+"],
				["<109", "109-147", "148-197", "198+"],
				["<113", "113-152", "153-203", "204+"],
				["<117", "117-157", "158-210", "211+"],
				["<121", "121-163", "164-217", "218+"],
				["<126", "126-168", "169-224", "225+"],
				["<130", "130-173", "174-231", "232+"],
				["<135", "135-179", "180-239", "240+"],
				["<140", "140-185", "186-246", "247+"],
				["<144", "144-190", "191-254", "255+"],
				["<149", "149-196", "197-261", "270+"],
				["<154", "154-202", "203-269", "262+"],
				["<159", "159-208", "209-277", "278+"],
				["<164", "164-214", "215-285", "286+"],
				["<169", "169-220", "221-293", "294+"],
				["<174", "174-226", "227-301", "302+"],
				["<179", "179-232", "233-310", "311+"],
				["<185", "185-239", "240-318", "319+"],
				["<190", "190-245", "246-327", "328+"]
			];
			KnowYourNumbers.otherWeightOptions = [
				["<119", "119-142", "143-190", "191+"],
				["<124", "124-147", "148-197", "198+"],
				["<128", "128-152", "153-203", "204+"],
				["<132", "132-157", "158-210", "211+"],
				["<136", "136-163", "164-217", "218+"],
				["<141", "141-168", "169-224", "225+"],
				["<145", "145-173", "174-231", "232+"],
				["<150", "150-179", "180-239", "240+"],
				["<155", "155-185", "186-246", "247+"],
				["<159", "159-190", "191-254", "255+"],
				["<164", "164-196", "197-261", "270+"],
				["<169", "169-202", "203-269", "262+"],
				["<174", "174-208", "209-277", "278+"],
				["<179", "179-214", "215-285", "286+"],
				["<184", "184-220", "221-293", "294+"],
				["<189", "189-226", "227-301", "302+"],
				["<194", "194-232", "233-310", "311+"],
				["<200", "200-239", "240-318", "319+"],
				["<205", "205-245", "246-327", "328+"]
			];
			KnowYourNumbers.isAsianAmerican = function() {
				if ($('[name="kyn_ethnicity"] option:selected').text() == 'Asian American') {
					return true;
				} else {
					return false;
				}
			}
			KnowYourNumbers.updateWeightOptions = function() {
				// there are 19 height options, starting at 4'10", ending at 6'4" (indexed 1 - 19)
				var height_radio = $("input[name='kyn_height___radio']:checked");
				var selected_height_index = height_radio.val();
				var option_labels;
				if (KnowYourNumbers.isAsianAmerican()) {
					option_labels = KnowYourNumbers.asianWeightOptions[selected_height_index - 1];
				} else {
					option_labels = KnowYourNumbers.otherWeightOptions[selected_height_index - 1];
				}
				
				option_labels.forEach(function(weight_label, i) {
					$("#label-kyn_weight-" + i).text(weight_label);
				});
			}
			$(function() {
				$("body").on('change', "input[name='kyn_height___radio'], select[name='kyn_ethnicity']", function(event) {
					KnowYourNumbers.updateWeightOptions();
				});
				
				// in case user is returning to page and branching logic for kyn_weight is satisfied
				KnowYourNumbers.updateWeightOptions();
			});
		</script>
		<?php
	}
	
	function getKnowYourNumbersScore($kyn_data) {
		$score = 0;
		$relevant_fields = [
			"kyn_family",
			"kyn_high_bp",
			"kyn_age",
			"kyn_active",
			"kyn_sex",
			"kyn_gestational",
			"kyn_weight"
		];
		foreach($relevant_fields as $field_name) {
			$score += intval($kyn_data->$field_name);
		}
		return $score;
	}
	
	function renderKnowYourNumbersCompleted($score, $record_id) {
		$score = intval($score);
		$high_risk = $score > 4;
		$risk_text = $high_risk ? "HIGH RISK" : "LOW RISK";
		$kyn_rubric = file_get_contents($this->getURL('docs/kyn_rubric.html'));
		$kyn_rubric = str_replace(array("\r\n", "\n", "\r"), '', $kyn_rubric);
		$kyn_about_risk_test = file_get_contents($this->getURL('docs/kyn_about_risk_test.html'));
		$kyn_about_risk_test = str_replace(array("\r\n", "\n", "\r"), '', $kyn_about_risk_test);
		$retake_survey_url = \REDCap::getSurveyLink($record_id, "know_your_numbers", null, null, $this->getProjectId());
		$header = <<<HEREDOC
		<div id='dpp_kyn_container'>
			<div class='gray-kyn-header'>
				<p>YOUR SCORE: <span class='kyn-green-text'>$score</span> of <span class='kyn-green-text'>10</span></p>
				<p>(<span class='kyn-green-text'>$risk_text</span> FOR DIABETES)</p>
				<p><a class='kyn-green-text toggle_rubric' href='#'>How Your Test is Scored</a></p>
			</div>
HEREDOC;
		if ($high_risk) {
			$results_pdf_url = "https://www.cdc.gov/diabetes/widgets/risktest/pdf/prediabetes{$score}of10.pdf";
			$mailto_email_body = <<<HEREDOC
Based on your results, you're likely to have prediabetes, but only your doctor can diagnose it for sure. Click on the link below to print your results, bring them to your doctor, and ask for a simple blood test to confirm them.

$results_pdf_url 

About the prediabetes risk test:
www.cdc.gov/diabetes/takethetest/about-the-test.html

HEREDOC;
			$mailto_email_subject = "Your Prediabetes Test Results Score";
			$mailto_url = "mailto:?subject=\"$mailto_email_subject\"&body=\"$mailto_email_body\"";
			
			$content = <<<HEREDOC
			<div class='subdiv'>
				<p>Based on your score, you're likely to have prediabetes, but only your doctor can diagnose it for sure. Share your results with your doctor and ask for a simple blood test to confirm them.</p>
				<div id='share_buttons'>
					<a class='kyn-green-text' href='$mailto_url'><i class='fas fa-envelope'></i> Email your results</a>
					<a class='kyn-green-text' href='$results_pdf_url'><i class='fas fa-print'></i> Print your results</a>
				</div>
				<h3>What Can I Do Next?</h3>
				<div class='shaded'>
					<div>
						<p><b>I want to talk to my provider.</b></p>
						<p>If you are already established with a primary care provider, please reach out to their office to schedule an appointment. If not, please call this number to get established: 615-343-8863</p>
					</div>
				</div>
				<div class='shaded'>
					<div>
						<p><b>I'm ready to get started.</b></p>
						<p>CDC's National Diabetes Prevention Program lifestyle change program gives you the steps you need to cut your risk for type 2 diabetes in half.</p>
						<a class='kyn-green-text' href='https://www.vumc.org/health-wellness/service-articles-health-plus/diabetes-prevention-program'>Take me to the program <i class='fas fa-arrow-right'></i></a>
					</div>
				</div>
				<div class='subdiv'>
					<a class='kyn-green-text' href='$retake_survey_url'>Take again</a>
				</div>
			</div>
		</div>
		$kyn_rubric
		$kyn_about_risk_test
HEREDOC;
		} else {
			$content = <<<HEREDOC
			<div class='subdiv'>
				<p>Based on your results, you're at a low risk for prediabetes. Keep up the good work! These healthy habits will help keep your risk low:</p>
				<ul>
					<li>Get at least 150 minutes of physical activity a week.</li>
					<li>Keep your weight in a healthy range.</li>
					<li>Eat healthy foods, including lots of fruits and veggies.</li>
					<li>Drink more water and fewer sugary drinks.</li>
					<li>Don't smoke.</li>
				</ul>
				<p><a class='kyn-green-text toggle_about_risk_test' href='#'>About the risk test</a></p>
				<p><a class='kyn-green-text' href='$retake_survey_url'>Take again</a></p>
			</div>
		</div>
		$kyn_rubric
		$kyn_about_risk_test
HEREDOC;
		}
		
		
		// remove newline characters in header and content
		$header = str_replace(array("\r\n", "\n", "\r"), '', $header);
		$content = str_replace(array("\r\n", "\n", "\r"), '', $content);
		$css_url = $this->getURL("css/kyn.css");
		
		?>
		<script type='text/javascript'>
			$(function() {
				$("#kyn_rubric").css('display', 'block');
				$("#kyn_about_risk_test").css('display', 'block');
				$("#kyn_rubric").hide();
				$("#kyn_about_risk_test").hide();
				
				$("#surveyacknowledgment").before("<?= $header . $content ?>");
				$("head").append('<?php echo "<link rel=\'stylesheet\' type=\'text/css\' href=\'$css_url\'>"; ?>');
				$('body').on('click', '.toggle_rubric', toggleRubric);
				$('body').on('click', '.toggle_about_risk_test', toggleAboutRiskTest);
			});
			
			function toggleRubric() {
				$("div#dpp_kyn_container").toggle();
				$("div#kyn_rubric").toggle();
			}
			function toggleAboutRiskTest() {
				$("div#dpp_kyn_container").toggle();
				$("div#kyn_about_risk_test").toggle();
			}
		</script>
		<?php
	}
	
	function onKnowYourNumbersCompleted($record, $event_id, $repeat_instance) {
		// fetch kyn answer values
		$project = new \Project($this->getProjectId());
		$kyn_fields = $project->forms['know_your_numbers']['fields'];
		$get_data_parameters = [
			"project_id" => $this->getProjectId(),
			"return_format" => "json",
			"records" => $record,
			"fields" => array_keys($kyn_fields)
		];
		$kyn_data = reset(json_decode(\REDCap::getData($get_data_parameters)));
		
		// calculate score
		$score = $this->getKnowYourNumbersScore($kyn_data);
		
		// save score to record
		$kyn_data->kyn_score = $score;
		$save_params = [
			"project_id" => $this->getProjectId(),
			"dataFormat" => "json",
			"data" => json_encode([$kyn_data])
		];
		$result = \REDCap::saveData($save_params);
		if (gettype($result) == 'string') {
			\REDCap::logEvent("DPP Module", "REDCap encountered an error while trying to save a participant's [kyn_score] value.
			record: $record
			kyn_score: $score
			error: $result");
		}
		if (!empty($result['errors'])) {
			\REDCap::logEvent("DPP Module", "REDCap encountered an error while trying to save a participant's [kyn_score] value.
			record: $record
			kyn_score: $score
			errors:
			" . implode("\n", $result['errors']));
		}
		$_GET['__endpublicsurvey'] = false;
		
		// render survey complete info paragraph
		$this->renderKnowYourNumbersCompleted($score, $record);
	}
	
	function redcap_survey_page($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
		if ($instrument == 'know_your_numbers') {
			$this->inKnowYourNumbersSurvey();
		}
	}
	
	function redcap_survey_complete($project_id, $record, $instrument, $event_id, $group_id, $survey_hash, $response_id, $repeat_instance) {
		if ($instrument == 'know_your_numbers') {
			$this->onKnowYourNumbersCompleted($record, $event_id, $repeat_instance);
		}
	}
	
	
	
}
