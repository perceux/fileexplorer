/**
 * 
 */
package org.dreamsoft.fileexplorer.client.dialog.model;

import java.io.Serializable;
import java.util.Date;

import com.extjs.gxt.ui.client.data.BaseModelData;
import com.extjs.gxt.ui.client.data.ModelData;

/**
 * A modle which represents a File (File means it can be a file or a directory
 * depending on the isDirectory returns)
 * 
 * @author Eric Taix
 */
@SuppressWarnings("serial")
public class FileModel extends BaseModelData implements ModelData, Serializable {

	public <X extends Object> X set(String property, X value) {
		String kext = null;
		if ("name".equals(property)) {
			String name = (String) value;
			set("shortName", (name != null && name.length() > 15) ? name.substring(0, 13) + "..." : name);
			String ext = "";
			if (name != null && name.indexOf('.') > -1) {
				ext = name.substring(1 + name.lastIndexOf('.'));
			}
			if (get("type") != null) {
				kext = getKnownExtension("dir".equals((String) get("type")), ext);
			}
			set("ext", ext);
		} else if ("type".equals(property) && get("ext") != null) {
			kext = getKnownExtension("dir".equals((String) value), (String) get("ext"));
		}
		if (kext != null) {
			set("icon48x48", "images/mime/48x48/" + kext + ".gif");
			set("icon16x16", "images/mime/16x16/" + kext + ".gif");
		}
		return super.set(property, value);
	};

	/**
	 * @return the name
	 */
	public String getName() {
		return (String) get("name");
	}

	/**
	 * @return the directory
	 */
	public boolean isDirectory() {
		return "dir".equals((String) get("type"));
	}

	/**
	 * @return the lastModified
	 */
	public Date getLastModified() {
		return (Date) get("mtime");
	}

	public Date getCreated() {
		return (Date) get("ctime");
	}

	/**
	 * @return the img16x16
	 */
	public String getIcon16x16() {
		return (String) get("icon16x16");
	}

	/**
	 * @return the img48x48
	 */
	public String getIcon48x48() {
		return (String) get("icon48x48");
	}

	public String getExtension() {
		return (String) get("ext");
	}

	private String getKnownExtension(boolean isDir, String ext) {
		if (isDir)
			return "dir";
		String[] knownExts = new String[] { "csv", "dir", "doc", "txt", "xls", "zip", "ppt" };
		String currentExt = ext;
		for (int i = 0; i < knownExts.length; i++) {
			if (currentExt.equalsIgnoreCase(knownExts[i]))
				return knownExts[i];
		}
		return "other";
	}

	public Long getSize() {
		return (Long) get("size");
	}

}
