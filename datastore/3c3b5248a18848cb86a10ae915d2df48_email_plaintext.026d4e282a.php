<?php

return <<<'VALUE'
"namespace IPS\\Theme;\n\tfunction email_html_core_upgrade( $version, $releaseNotes, $security, $email ) {\n\t\t$return = '';\n\t\t$return .= <<<CONTENT\n\n\n\nCONTENT;\n$return .= htmlspecialchars( $email->language->addToStack(\"dashboard_version_info\", FALSE, array( 'sprintf' => $version )), ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\n<br \/>\n<br \/>\n\nCONTENT;\n\nif ( $security ):\n$return .= <<<CONTENT\n\n\t<table width='100%' cellpadding='15' cellspacing='0' border='0' style='background: #b52b38; color: #fff;'>\n\t\t<tr>\n\t\t\t<td dir='{dir}'>\n\t\t\t\t\nCONTENT;\n$return .= htmlspecialchars( $email->language->addToStack('this_is_a_security_update'), ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\n\t\t\t<\/td>\n\t\t<\/tr>\n\t<\/table>\n\t<br \/>\n\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n\n\n<table width='100%' cellpadding='10' cellspacing='0' border='0'>\n\t<tr>\n\t\t<td dir='{dir}' valign='top' style='background: #f9f9f9;'>\n\t\t\t{$releaseNotes}\n\t\t<\/td>\n\t<\/tr>\n<\/table>\n<br \/><br \/>\n\n<a href='\nCONTENT;\n\n$return .= str_replace( '&', '&amp;', \\IPS\\Http\\Url::internal( \"&app=core&module=system&controller=upgrade\", \"admin\", \"\", array(), 0 ) );\n$return .= <<<CONTENT\n' style=\"color: #fff; font-family: 'Helvetica Neue', helvetica, sans-serif; text-decoration: none; font-size: 12px; background: \nCONTENT;\n\n$return .= \\IPS\\Settings::i()->email_color;\n$return .= <<<CONTENT\n; line-height: 32px; padding: 0 10px; display: inline-block; border-radius: 3px;\">\nCONTENT;\n$return .= htmlspecialchars( $email->language->addToStack(\"upgrade_now\"), ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n<\/a>\n\nCONTENT;\n\n\t\treturn $return;\n}\n\tfunction email_plaintext_core_upgrade( $version, $releaseNotes, $security, $email ) {\n\t\t$return = '';\n\t\t$return .= <<<CONTENT\n\n\nCONTENT;\n$return .= htmlspecialchars( $email->language->addToStack(\"dashboard_version_info\", FALSE, array( 'sprintf' => $version )), ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\n\nCONTENT;\n\nif ( $security ):\n$return .= <<<CONTENT\n\n\nCONTENT;\n$return .= htmlspecialchars( $email->language->addToStack('this_is_a_security_update'), ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n\n\nCONTENT;\n\nendif;\n$return .= <<<CONTENT\n\n\n\nCONTENT;\n$return .= htmlspecialchars( $email->language->addToStack(\"upgrade_now\"), ENT_QUOTES | \\IPS\\HTMLENTITIES, 'UTF-8', FALSE );\n$return .= <<<CONTENT\n: \nCONTENT;\n\n$return .= str_replace( '&', '&amp;', \\IPS\\Http\\Url::internal( \"&app=core&module=system&controller=upgrade\", \"admin\", \"\", array(), 0 ) );\n$return .= <<<CONTENT\n\nCONTENT;\n\n\t\treturn $return;\n}"
VALUE;
