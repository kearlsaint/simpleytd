$(function(){$("[hideme]").hide();$("[inputget]").on("click",function(a){var t=$("[inputurl]").val(),e=$("<div>");e.html("Getting "+t.truncate(40)),$("[jsjs]").after(e),$("[inputurl]").val(""),$.ajax({type:"POST",url:"index.php",data:{token:$token,fragment:!0,link:t},dataType:"JSON",success:function(a){if(void 0!=a.title&&a.data.length>0){$fragment=$("<div>"),$fragment.append("<hr>"),$fragment.append("<div><a white href=\"" + a.link + "\">"+a.title+"</a></div>"),$list=$("<ul>");for(var t=0;t<a.data.length;t++)$list.append('<li><small><a href="'+a.data[t].url+'&filename='+encodeURIComponent(a.title)+'&filetype='+a.data[t].filetype+'" download target="_blank">'+a.data[t].type+"</a></small></li>");$fragment.append($list),e.html("").append($fragment)}else e.html("<hr><span red>Server error! Boo hoo.</span>")},error:function(a){e.html("<hr><span red>Network Error! Please try again.</span>")}}),a.preventDefault()})});