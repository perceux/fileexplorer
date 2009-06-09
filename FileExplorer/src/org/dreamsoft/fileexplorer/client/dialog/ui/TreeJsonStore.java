package org.dreamsoft.fileexplorer.client.dialog.ui;

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
		JsonReader<ListLoadResult<ModelData>> reader = new JsonReader<ListLoadResult<ModelData>>(mt);
		TreeLoader<D> loader = new BaseTreeLoader<D>(proxy2, reader) {
			@Override
			public boolean hasChildren(D parent) {
				return true;//"true".equals(parent.get("haveChildren").toString());
			}

			@Override
			protected Object prepareLoadConfig(Object config) {
				// by default the load config will be the parent model
				// http proxy will set all properties of model into request
				// paramerters, so the model name and id will be passed to
				// server
				return super.prepareLoadConfig(config);
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
				Info.display("Loading Error", "" + le.exception.getMessage());
			}

		});

		// ---------------------------------------------

	}

	public TreeStore<D> getStore() {
		return store;
	}

}