<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" 
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:g="http://base.google.com/ns/1.0">
    <xsl:output method="html" encoding="UTF-8" indent="yes"/>
    <xsl:strip-space elements="*"/>

    <!-- Main template -->
    <xsl:template match="/rss/channel">
        <html xmlns="http://www.w3.org/1999/xhtml">
            <head>
                <title>Product Feed</title>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css"/>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    h1 {
                        text-align: center;
                        margin-bottom: 20px;
                    }
                    .table {
                        width: 100%;
                        margin-bottom: 20px;
                    }
                    .table th, .table td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    .table tr:nth-child(even) {
                        background-color: #f2f2f2;
                    }
                    .table tr:hover {
                        background-color: #ddd;
                    }
                    .table th {
                        padding-top: 12px;
                        padding-bottom: 12px;
                        background-color: #4CAF50;
                        color: white;
                    }
                    .img-fluid {
                        max-width: 100px;
                        height: auto;
                    }
                </style>
            </head>
            <body>
                <h1>Products Feed</h1>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Link</th>
                            <th>Condition</th>
                            <th>Color/Size</th>
                            <th>Availability</th>
                            <th>Price</th>
                            <th>Google Product Category</th>
                            <th>Product Type</th>
                            <th>Brand</th>
                            <th>Item Group ID</th>
                            <th>Gender</th>
                            <th>Adult</th>
                            <th>Size Type</th>
                            <th>Size System</th>
                            <th>Tax Rate</th>
                            <th>Tax Country</th>
                            <th>Shipping Weight</th>
                            <th>Promotion ID</th>
                            <th>Additional Images</th>
                        </tr>
                    </thead>
                    <tbody>
                        <xsl:for-each select="item">
                            <tr>
                                <td>
                                    <img src="{g:image_link}" alt="{g:title}" class="img-fluid"/>
                                </td>
                                <td><xsl:value-of select="g:id"/></td>
                                <td><xsl:value-of select="g:title"/></td>
                                <td><xsl:value-of select="g:description"/></td>
                                <td><a href="{g:link}" target="_blank">View Product</a></td>
                                <td><xsl:value-of select="g:condition"/></td>
                                <td><xsl:value-of select="g:color | g:size"/></td>
                                <td><xsl:value-of select="g:availability"/></td>
                                <td><xsl:value-of select="g:price"/> <xsl:text> INR</xsl:text></td>
                                <td><xsl:value-of select="g:google_product_category"/></td>
                                <td><xsl:value-of select="g:product_type"/></td>
                                <td><xsl:value-of select="g:brand"/></td>
                                <td><xsl:value-of select="g:item_group_id"/></td>
                                <td><xsl:value-of select="g:gender"/></td>
                                <td><xsl:value-of select="g:adult"/></td>
                                <td><xsl:value-of select="g:size_type"/></td>
                                <td><xsl:value-of select="g:size_system"/></td>
                                <td><xsl:value-of select="g:tax/g:rate"/>%</td>
                                <td><xsl:value-of select="g:tax/g:country"/></td>
                                <td><xsl:value-of select="g:shipping_weight"/></td>
                                <td><xsl:value-of select="g:promotion_id"/></td>
                                <td>
                                    <xsl:for-each select="g:additional_image_link">
                                        <a href="{.}" target="_blank">
                                            <img src="{.}" alt="Additional Image" class="img-fluid" style="margin-right: 5px;"/>
                                        </a>
                                    </xsl:for-each>
                                </td>
                            </tr>
                        </xsl:for-each>
                    </tbody>
                </table>
            </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
