# f_form
WordPress Plugin for a form to upload to server.
Simple Contact Form for WordPress.
Uses SFTPConnection class to upload files.
Use shortcode "[f_form]" to create a page in WordPress with this form on it.
The form's drop down list of file folder options comes from the ksqd.info server.
There is an option to trim time from the beginning of the audio file input. (Uses ffmpeg to strip beginning from file.)
First the file uploads to the server, then is transferred to the secure server.
Recaptcha is currently disabled - so be sure to put this shortcode on a private page.
Only the following input file extensions are allowed: 'wav', 'aiff', 'ogg', 'mp3', 'aac', 'wma', 'alac'.
If successful, page will show URL of uploaded file. User should copy this for use in their posts.
There are some server limits which restrict some files from succeeding, such as file size and upload time.
Some slow internet connections may time out, unfortunately.
