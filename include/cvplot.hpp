// Matlab style plot functions for OpenCV by Changbo (zoccob@gmail).
// plot and label:
//
// template<typename T>
// void plot(const string figure_name, const T* p, int count, int step = 1,
//		     int R = -1, int G = -1, int B = -1);
//
// figure_name: required. multiple calls of this function with same figure_name
//              plots multiple curves on a single graph.
// p          : required. pointer to data.
// count      : required. number of data.
// step       : optional. step between data of two points, default 1.
// R, G,B     : optional. assign a color to the curve.
//              if not assigned, the curve will be assigned a unique color automatically.
//
// void label(string lbl):
//
// label the most recently added curve with lbl.
//
//
//
//

#pragma once

#if WIN32
#define snprintf sprintf_s
#endif

#include <vector>
#include <opencv2/highgui/highgui.hpp>

using namespace std;
using namespace cv;

namespace CvPlot
{
	// A curve.
	class Series
	{
	public:

		// number of points
		unsigned int count;
		float *data;
		// name of the curve
		string label;

		// allow automatic curve color
		bool auto_color;
		Scalar color;
        Scalar *bg_color;

		Series(void);
		Series(const Series& s);
		~Series(void);

		// release memory
		void clear();

		void setData(int n, float *p, Scalar* background_color);

		void setColor(const Scalar color, bool auto_color = true);

		void setBackgroundColor(Scalar* color);
	};

	// a figure comprises of several curves
	class Figure
	{
	private:
		// window name
		string figure_name;
        cv::Size figure_size;

		// margin size
		int border_size;

		Scalar figure_color;
		Scalar axis_color;
		Scalar text_color;

		// several curves
		vector<Series> plots;

		// manual or automatic range
		bool custom_range_y;
		float y_max;
		float y_min;

		float y_scale;

		bool custom_range_x;
		float x_max;
		float x_min;

		float x_scale;

		// automatically change color for each curve
		int color_index;

	public:
		Figure(const string name);
		~Figure();

		string getFigureName();
		Series* add(const Series &s);
		void clear();
		void drawLabels(Mat *output, int posx, int posy);

		// show plot window
		void show();

        // get image
        Mat getImage();

	private:
		Figure();
		void drawAxis(Mat *output);
		void drawPlots(Mat *output);

		// call before plot
		void initialize();
		Scalar getAutoColor();
	};

	// manage plot windows
	class PlotManager
	{
	private:
		vector<Figure> figure_list;
		Series *active_series;
		Figure *active_figure;

	public:
		Figure* findFigure(string wnd);

		void plot(const string figure_name, const float* p, int count, int step, const Scalar line_color, Scalar* background_color);

		void label(string lbl);

		Mat getImage(const string figure_name);
	};

	// handle different data types; static mathods;

	template<typename T>
	void plot(const string figure_name, const T* p, int count, int step = 1, const Scalar line_color = Scalar(0,0,0,0), Scalar* background_color = NULL);

	void label(string lbl);

	Mat getImage(const string figure_name);

	void clear(const string figure_name);
};
