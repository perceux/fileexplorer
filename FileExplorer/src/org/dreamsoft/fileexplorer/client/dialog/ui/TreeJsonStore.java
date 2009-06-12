package org.dreamsoft.fileexplorer.client.dialog.ui;

import java.util.List;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.data.BaseTreeLoader;
import com.extjs.gxt.ui.client.data.JsonReader;
import com.extjs.gxt.ui.client.data.ListLoadResult;
import com.extjs.gxt.ui.client.data.LoadEvent;
import com.extjs.gxt.ui.client.data.ModelData;
import com.extjs.gxt.ui.client.data.ModelType;
import com.extjs.gxt.ui.client.data.ScriptTagProxy;
import com.extjs.gxt.ui.client.data.TreeLoader;
import com.extjs.gxt.ui.client.event.LoadListener;
import com.extjs.gxt.ui.client.store.TreeStore;
import com.extjs.gxt.ui.client.widget.Info;
import com.google.gwt.user.client.Window;

public class TreeJsonStore<D extends ModelData> {

	protected TreeStore<D> store;

	public TreeJsonStore(ModelType mt, String url, ScriptTagProxy<ListLoadResult<FileModel>> proxy2) {
		//url += "?cache=" + new Date().getTime();
		proxy2.setUrl(url);
		// for cache issue because IE
		// doesn't clear cache
		// so....
//		RequestBuilder builder = new RequestBuilder(RequestBuilder.GET, url);
//		HttpProxy<ListLoadResult<ModelData>> proxy = new HttpProxy<ListLoadResult<ModelData>>(builder);
		JsonReader<List<FileModel>> reader = new JsonReader<List<FileModel>>(mt) {
			@Override
			public List<FileModel> read(Object loadConfig, Object data) {
				ModelData config = (ModelData) loadConfig;
				List<FileModel> l = super.read(loadConfig, data);
				for (FileModel fileModel : l) {
					fileModel.set("path", config.get("src"));
				}
				return l;
			}
			@Override
			protected ModelData newModelInstance() {
				return new FileModel();
			}
		};

		TreeLoader<D> loader = new BaseTreeLoader<D>(proxy2, reader) {
			public boolean loadChildren(D parent) {
				Window.alert(""+parent.getClass());
				
				return super.loadChildren(parent);
			};
			@Override
			public boolean hasChildren(D parent) {
				return "true".equals(parent.get("leaf").toString());
			}

			@Override
			protected Object prepareLoadConfig(Object config) {
				// by default the load config will be the parent model
				// http proxy will set all properties of model into request
				// paramerters, so the model name and id will be passed to
				// server
				FileModel fm = new FileModel();
				Window.alert(""+config.getClass());
				
				if (config instanceof FileModel) {
					FileModel md = (FileModel) config;
					fm.set("src", md.getPathName());
				}
				return fm;
			}
		};

		store = new TreeStore<D>(loader);
		
		loader.addLoadListener(new LoadListener() {

			@Override
			public void loaderLoad(LoadEvent le) {
				Info.display("load", "complete");
			}

			@Override
			public void loaderLoadException(LoadEvent le) {
				le.exception.printStackTrace();
				Info.display("Loading Error", "" + le.exception.getMessage());
			}

		});

		// ---------------------------------------------

	}

	public TreeStore<D> getStore() {
		return store;
	}

}