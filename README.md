# Cloud Poodll Recording Question for Moodle

**A native Moodle question type that lets students answer by recording audio, video, or drawing
on a whiteboard.**

Cloud Poodll adds a question type to the Moodle question bank, so it works in quizzes anywhere a
standard question would — students record their response directly in the browser, and the
recording is stored in the Poodll cloud rather than on your Moodle server. Like an Essay
question, responses are graded manually by a teacher, with the recording (and, optionally, its
transcript) shown alongside for grading.

- **Plugin:** `qtype_cloudpoodll` (question type)
- **Maintainer:** Justin Hunt — poodllsupport@gmail.com
- **Documentation:** https://support.poodll.com
- **License:** GNU GPL v3 or later

---

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Site configuration](#site-configuration)
- [Response formats](#response-formats)
- [Authoring a question](#authoring-a-question)
- [Grading](#grading)
- [Privacy](#privacy)
- [Support](#support)

---

## Requirements

| | |
|---|---|
| Moodle | 4.3 or later (`$plugin->requires = 2023100900`) |
| PHP | 8.1+ (PHP 8.4 supported) |
| Database | MySQL / MariaDB / PostgreSQL |
| Cloud Poodll account | **Required.** An API user and secret from https://poodll.com |

Cloud Poodll relies on the Cloud Poodll service for recording, storage, transcoding and speech
transcription. Without API credentials the question type will not function.
See [Cloud Poodll API secret](https://support.poodll.com/support/solutions/articles/19000083076-cloud-poodll-api-secret)
for how to obtain them.

## Installation

1. Copy the plugin folder to `question/type/cloudpoodll` in your Moodle code root (on Moodle
   5.1+ this is `public/question/type/cloudpoodll`).
2. Visit **Site administration → Notifications** and complete the upgrade.
3. Enter your Cloud Poodll API user and secret at
   **Site administration → Plugins → Question types → Cloud Poodll**.

## Site configuration

Settings live under **Site administration → Plugins → Question types → Cloud Poodll**:

- **API user / API secret**, 
- **AWS region**  The AWS region processing and data storage take place in, 
- **Cloud Poodll server** The default is fine. Users with AWS region Ningxia in China should use cloud.poodll.cn
- Expiry days for recordings.

Most of the day-to-day options (recorder type, skin, transcription, transcoding, etc.) are set
per question, on the question's own editing form, rather than site-wide.

## Response formats

When creating a question, the teacher picks a **response format**:

- **Audio** or **Video** — the student records using a Poodll recorder, with a choice of skins.
- **Whiteboard** *(beta)* — the student draws or annotates instead of recording; an optional
  background image (e.g. a diagram or passage) can be set for the student to draw on top of.

Other per-question options include a time limit, transcoding to mp3/mp4, disabling audio noise
filters (useful for music or shadowing exercises, where noise suppression would distort the
recording), and Safe Save (disables the next-page/finish button until the recording has finished
uploading, so a submission can't be lost).

## Authoring a question

Beyond the recording settings, a Cloud Poodll question supports:

- **Information for graders** — notes visible only to whoever grades the question, e.g. a model
  answer or marking guidance.
- **Transcription** — Cloud Poodll can transcribe the student's speaking and display it beneath
  the recording, both for the student reviewing their attempt and the teacher grading it.

## Grading

Cloud Poodll questions are graded manually, the same way Essay questions are — a teacher listens
to (or watches) each response and assigns a grade and comment through the normal Moodle question
grading interface.

## Privacy

This plugin stores personal data: question responses (recordings) and, if enabled,
transcripts. Recordings are stored via Cloud Poodll (AWS S3), and identifying information
appears in recording URLs. The plugin implements the Moodle Privacy API for export and deletion.

## Support

- Documentation and how-tos: https://support.poodll.com
- Account and subscriptions: https://member.poodll.com
- Contact: poodllsupport@gmail.com

## License

Copyright Justin Hunt / Poodll. Licensed under the
[GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html).
