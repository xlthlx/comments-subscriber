const preview = document.getElementById("csPreview");

preview.addEventListener("click", function (e) {
    e.preventDefault();

    let s = document.getElementById("subject").value;
    let m = document.getElementById("message").value;

    s = replace_tags('{title}', 'Sample Title', s);
    s = replace_tags('{name}', 'You', s);
    s = replace_tags('{author}', 'Bob', s);

    m = replace_tags('{title}', 'Sample Title', m);
    m = replace_tags('{name}', 'You', m);
    m = replace_tags('{author}', 'Bob', m);
    m = replace_tags("{content}", "I totally agree with your opinion about him, he's really...", m);
    m = replace_tags('{link}', '#', m);
    m = replace_tags('{comment_link}', '#', m);
    m = replace_tags('{unsubscribe}', '#', m);
    m = m.replace(/\n/g, "<br />");
    const h = window.open("", "cs", "status=0,toolbar=0,scrollbars=1,height=400,width=550");
    const d = h.document;
    d.write('<html lang="en"><head><title>Email preview</title>');
    d.write('</head><body>');
    d.write('<table style="width:100%;border:1px solid #ccc;border-spacing:0;">');
    d.write('<tr><td style="padding:5px;text-align:right"><b>Subject</b></td><td>' + s + '</td></tr>');
    d.write('<tr><td style="padding:5px;text-align:right"><b>From</b></td><td>' + document.getElementById("from_name").value + ' &lt;' + document.getElementById("from_email").value + '&gt;</td></tr>');
    d.write('<tr><td style="padding:5px;text-align:right"><b>To</b></td><td>User name &lt;user@email&gt;</td></tr>');
    d.write('<tr><td style="padding:5px;text-align:left" colspan="2">' + m + '</td></tr>');
    d.write('</table>');
    d.write('</body></html>');
    d.close();
    return false;
});

function replace_tags(tag, replacement, text) {
    let intIndexOfMatch = text.indexOf(tag);
    while (intIndexOfMatch !== -1) {
        text = text.replace(tag, replacement);
        intIndexOfMatch = text.indexOf(tag);
    }
    return text;
}