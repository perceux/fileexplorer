package org.dreamsoft.fileexplorer.client.dialog.controler;

import java.util.Date;
import java.util.List;

import org.dreamsoft.fileexplorer.client.dialog.model.FileModel;

import com.extjs.gxt.ui.client.Registry;
import com.extjs.gxt.ui.client.data.BasePagingLoadResult;
import com.extjs.gxt.ui.client.data.BasePagingLoader;
import com.extjs.gxt.ui.client.data.DataField;
import com.extjs.gxt.ui.client.data.JsonLoadResultReader;
import com.extjs.gxt.ui.client.data.ListLoadResult;
import com.extjs.gxt.ui.client.data.LoadEvent;
import com.extjs.gxt.ui.client.data.ModelData;
import com.extjs.gxt.ui.client.data.ModelType;
import com.extjs.gxt.ui.client.data.PagingLoadConfig;
import com.extjs.gxt.ui.client.data.PagingLoadResult;
import com.extjs.gxt.ui.client.data.ScriptTagProxy;
import com.extjs.gxt.ui.client.event.LoadListener;
import com.extjs.gxt.ui.client.mvc.AppEvent;
import com.extjs.gxt.ui.client.mvc.Controller;
import com.extjs.gxt.ui.client.store.GroupingStore;
import com.extjs.gxt.ui.client.store.ListStore;
import com.extjs.gxt.ui.client.widget.Info;
import com.extjs.gxt.ui.client.widget.InfoConfig;
import com.google.gwt.user.client.Window;

public class StoreController extends Controller {

	private String url;
	private BasePagingLoader<PagingLoadResult<FileModel>> loader;
	private ListStore<FileModel> store;
	private FileListLoadConfig loadConfig = new FileListLoadConfig();
	private ScriptTagProxy<PagingLoadResult<FileModel>> proxy = new ScriptTagProxy<PagingLoadResult<FileModel>>("") {
		
		protected String generateUrl(Object loadConfig) {
			String s = super.generateUrl(loadConfig);
			InfoConfig ifg = new InfoConfig("url", url + "<br>" + s);
			ifg.width = 600;
			ifg.display = 4000;
			Info.display(ifg);
			return s;
		}
	};

	public StoreController(String url) {
		this.url = url;
		initStore();
		registerEventTypes(FilesEvents.DIRECTORY_CHANGED, FilesEvents.SITE_CHANGED);
	}

	public String getUrl() {
		return url;
	}

	@Override
	public void handleEvent(AppEvent eventP) {
		if (eventP.getType().equals(FilesEvents.SITE_CHANGED)) {
			updateUrl((String) eventP.getData());
		}
		// The current directory have been changed : update the file listing
		else if (eventP.getType().equals(FilesEvents.DIRECTORY_CHANGED)) {
			updateDirectory((String) eventP.getData());
		}
	}

	/**
	 * Update the content of the current directory
	 * 
	 * @param newDirP
	 */
	private void updateDirectory(String newSourceDir) {
		loadConfig.setSource(newSourceDir);
		loader.load(loadConfig);
	}

	public void updateUrl(String url) {
		proxy.setUrl(url);
		loader.load(loadConfig);
	}

	private void initStore() {
		String sortField = "name";
		String symbol = "ds01";

		ModelType mt = new ModelType();
		mt.setRoot("files");
		mt.setTotalName("total");
		mt.addField("name");
		DataField df1 = new DataField("mtime", "mtime");
		df1.setType(Date.class);
		df1.setFormat("timestamp");
		mt.addField(df1);
		DataField df2 = new DataField("ctime", "ctime");
		df2.setType(Date.class);
		df2.setFormat("timestamp");
		mt.addField(df2);
		DataField df3 = new DataField("size", "size");
		df3.setType(Long.class);
		mt.addField(df3);
		mt.addField("type");

		JsonLoadResultReader<PagingLoadResult<FileModel>> reader = new JsonLoadResultReader<PagingLoadResult<FileModel>>(mt) {
			@Override
			protected ListLoadResult<ModelData> newLoadResult(Object loadConfig, List<ModelData> models) {
				PagingLoadConfig pagingConfig = (PagingLoadConfig) loadConfig;
				PagingLoadResult<ModelData> result = new BasePagingLoadResult<ModelData>(models, pagingConfig.getOffset(), pagingConfig.getLimit());
				return result;
			}
			
			@Override
			protected ModelData newModelInstance() {
				return new FileModel();
			}
		};
		loader = new BasePagingLoader<PagingLoadResult<FileModel>>(proxy, reader);

		loader.addLoadListener(new LoadListener() {
			public void loaderBeforeLoad(LoadEvent le) {
				Info.display("JData", "Staring Loading....");
			}

			public void loaderLoad(LoadEvent le) {
				Info.display("JData", "Loading complete:" + loader.getTotalCount());
				FilesEvents.fireStoreChanged(store);
			}

			public void loaderLoadException(LoadEvent le) {
				InfoConfig ic = new InfoConfig("JData", "" + le.exception.getLocalizedMessage());
				ic.width = 600;
				Info.display(ic);
				Window.alert("" + le.exception);
			}
		});

		loader.setSortField(sortField);
		loader.setRemoteSort(false);
		store = new ListStore<FileModel>(loader);
		Registry.register(symbol, store);
		loader.load();
	}
}
