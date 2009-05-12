package org.dreamsoft.fileexplorer.client.dialog.model;

import java.io.Serializable;

import com.extjs.gxt.ui.client.data.BaseModelData;
import com.extjs.gxt.ui.client.data.ModelData;

@SuppressWarnings("serial")
public class SiteModel extends BaseModelData implements ModelData, Serializable {
	private String name;
	private String url;
	private String comment;
	private String imageUrl;
	private String baseDir;

	public SiteModel(String name, String url, String comment, String imageUrl, String baseDir) {
		set("name", name);
		set("url", url);
		set("comment", comment);
		set("imageUrl", imageUrl);
		set("baseDir", baseDir);
		this.name = name;
		this.url = url;
		this.comment = comment;
		this.imageUrl = imageUrl;
		this.baseDir = baseDir;
	}

	public String getName() {
		return (name==null)?"":name;
	}

	public String getUrl() {
		return (url==null)?"":url;
	}

	public String getComment() {
		return (comment==null)?"":comment;
	}

	public String getImageUrl() {
		return (imageUrl==null)?"":imageUrl;
	}

	public String getBaseDir() {
		return (baseDir==null)?"":baseDir;
	}

}
