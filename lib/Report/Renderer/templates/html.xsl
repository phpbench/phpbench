<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:output method="html" indent="yes" encoding="UTF-8"/>
    <xsl:param name="date" select="string('')"/>
    <xsl:param name="title" select="string('PHPBench Suite Results')"/>

    <xsl:template match="/reports">
        <xsl:text disable-output-escaping='yes'>&lt;!DOCTYPE html&gt;</xsl:text>
        <html lang="en">
            <head>
                <title>PHPBench</title>
                <style>
                    table {
                        border-collapse: collapse;
                    }
                    table, th, td {
                    border: 1px solid black;
                    padding: 0.5em;
                    }
                    table th {
                    background-color: #333;
                    color: #fff;
                    }
                    .footer p {
                    margin: 1em;
                    }
                </style>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            </head>
            <body>
                <center>
                    <div class="header">
                        <h1><xsl:value-of select="$title" /></h1>
                    </div>
                    <div class="body">
                        <xsl:apply-templates match="./report"/>
                    </div>
                    <div class="footer">
                        <p>
                            Generated  <xsl:value-of select="$date" /> by <a href="https://github.com/phpbench/phpbench">PHPBench</a> v<xsl:value-of select="$phpbench-version"/>
                        </p>
                        <p>
                            <a href="https://github.com/phpbench/phpbench"><img alt="Embedded Image" src="data:image/png;base64,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"/></a>
                        </p>
                    </div>
                </center>
            </body>
        </html>
    </xsl:template>

    <xsl:template match="report">
        <xsl:if test="@title != ''">
            <h2><xsl:value-of select="@title"/></h2>
        </xsl:if>
        <xsl:if test="@description != ''">
            <p><xsl:value-of select="description" /></p>
        </xsl:if>
        <xsl:apply-templates select="./table" />
    </xsl:template>

    <xsl:template match="table">
        <xsl:if test="@title != ''">
            <h3><xsl:value-of select="@title"/></h3>
        </xsl:if>
        <table>
            <thead>
                <tr>
                    <xsl:for-each select=".//col">
                        <th><xsl:value-of select="@label" /></th>
                    </xsl:for-each>
                </tr>
            </thead>
            <tbody>
            <xsl:for-each select=".//row">
                <tr>
                    <xsl:for-each select="cell">
                        <td><xsl:value-of select="."/></td>
                    </xsl:for-each>
                </tr>
            </xsl:for-each>
            </tbody>
        </table>
    </xsl:template>

</xsl:stylesheet>
