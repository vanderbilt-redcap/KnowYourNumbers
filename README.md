
**Know Your Numbers REDCap External Module Documentation**

This external module was created to help administer Know Your Numbers surveys.

**Know Your Numbers survey instrument**

The module expects the host project to have a survey instrument named "Know Your Numbers". The module will show survey participants extra information related to the Know Your Numbers program. Upon completing the survey, the module will calculate the participant's risk score and save the (1-10) value to the record's [kyn_score] field.

<img src="/docs/extra_kyn_info.png" alt="Extra survey information" width="50%"/>

**Fields**

The Know Your Numbers survey instrument should contain the following fields:
- [kyn_family]
- [kyn_high_bp]
- [kyn_age]
- [kyn_active]
- [kyn_sex]
- [kyn_gestational]
- [kyn_weight]
- [kyn_score]

You may wish set the [kyn_score] field's action tags as '@HIDDEN' or '@HIDDEN-SURVEY'. Doing so will not affect the module's ability to calculate or save the score value.

**Error Logging**

When the module encounters an error and fails to save a [kyn_score] value, it will log the error message to the project's Logging page:

![Error logging](/docs/kyn_logging.png)
