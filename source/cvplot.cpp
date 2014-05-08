#include <cstdio>
#include <iostream>
#include "cvplot.hpp"

namespace CvPlot
{

//  use anonymous namespace to hide global variables.
namespace {
	const Scalar CV_BLACK = CV_RGB(0,0,0);
	const Scalar CV_WHITE = CV_RGB(255,255,255);
	const Scalar CV_GREY = CV_RGB(150,150,150);

	PlotManager pm;
}


Series::Series(void) {
	data = NULL;
    bg_color = NULL;
	label = "";
	clear();
}

Series::Series(const Series& s):count(s.count), label(s.label), auto_color(s.auto_color), color(s.color) {
	data = new float[count];
    bg_color = new Scalar[count];
	memcpy(data, s.data, count * sizeof(float));
    memcpy(bg_color, s.bg_color, count * sizeof(Scalar));
}


Series::~Series(void) {
	clear();
}

void Series::clear(void) {
	if (data != NULL)
		delete[] data;
	data = NULL;

    if (bg_color != NULL)
        delete[] bg_color;
    bg_color = NULL;

	count = 0;
	color = CV_BLACK;
	auto_color = true;
	label = "";
}

void Series::setData(int n, float *p, Scalar* background_color) {
	clear();

	count = n;
	data = p;
    bg_color = new Scalar[count];
    setBackgroundColor(background_color);
}

void Series::setColor(Scalar color, bool auto_color) {
	this->color = color;
	this->auto_color = auto_color;
}

void Series::setBackgroundColor(Scalar* color) {
    for (int i=0; i < count; i++) {
        if(color != NULL) {
            this->bg_color[i] = color[i];
        } else {
            this->bg_color[i] = CV_WHITE;
        }
    }
}

Figure::Figure(const string name) {
	figure_name = name;

	custom_range_y = false;
	custom_range_x = false;
	figure_color = CV_WHITE;
	axis_color = CV_BLACK;
	text_color = CV_BLACK;

	figure_size = cv::Size(600, 200);
	border_size = 30;

	plots.reserve(10);
}

Figure::~Figure(void) {}

string Figure::getFigureName(void) {
	return figure_name;
}

Series* Figure::add(const Series &s) {
	plots.push_back(s);
	return &(plots.back());
}

void Figure::clear() {
      plots.clear();
}

void Figure::initialize() {
	color_index = 0;

	// size of the figure
	if (figure_size.width <= border_size * 2 + 100)
		figure_size.width = border_size * 2 + 100;
	if (figure_size.height <= border_size * 2 + 200)
		figure_size.height = border_size * 2 + 200;

	y_max = FLT_MIN;
	y_min = FLT_MAX;

	x_max = 0;
	x_min = 0;

	// find maximum/minimum of axes
	for (vector<Series>::iterator it = plots.begin(); it != plots.end(); it++) {
		float *p = it->data;
		for (int i=0; i < it->count; i++) {
			float v = p[i];
			if (v < y_min)
				y_min = v;
			if (v > y_max)
				y_max = v;
		}

		if (x_max < it->count)
			x_max = it->count;
	}

	// calculate zoom scale
	// set to 2 if y range is too small
	float y_range = y_max - y_min;
	float eps = 1e-9f;
	if (y_range <= eps) {
		y_range = 1;
		y_min = y_max / 2;
		y_max = y_max * 3 / 2;
	}

	x_scale = 1.0f;
	if (x_max - x_min > 1)
		x_scale = (float)(figure_size.width - border_size * 2) / (x_max - x_min);

	y_scale = (float)(figure_size.height - border_size * 2) / y_range;
}

Scalar Figure::getAutoColor() {
	// 	change color for each curve.
	Scalar col;

	switch (color_index) {
	case 1:
		col = CV_RGB(60,60,255);	// light-blue
		break;
	case 2:
		col = CV_RGB(60,255,60);	// light-green
		break;
	case 3:
		col = CV_RGB(255,60,40);	// light-red
		break;
	case 4:
		col = CV_RGB(0,210,210);	// blue-green
		break;
	case 5:
		col = CV_RGB(180,210,0);	// red-green
		break;
	case 6:
		col = CV_RGB(210,0,180);	// red-blue
		break;
	case 7:
		col = CV_RGB(0,0,185);		// dark-blue
		break;
	case 8:
		col = CV_RGB(0,185,0);		// dark-green
		break;
	case 9:
		col = CV_RGB(185,0,0);		// dark-red
		break;
	default:
		col =  CV_RGB(200,200,200);	// grey
		color_index = 0;
	}
	color_index++;
	return col;
}

void Figure::drawAxis(Mat *output) {
	int bs = border_size;
	int h = figure_size.height;
	int w = figure_size.width;

	// size of graph
	int gh = h - bs * 2;
	int gw = w - bs * 2;

	// draw the horizontal and vertical axis
	// let x, y axies cross at zero if possible.
	float y_ref = y_min;
	if ((y_max > 0) && (y_min <= 0))
		y_ref = 0;

	int x_axis_pos = h - bs - round((y_ref - y_min) * y_scale);

	line(*output, Point(bs, x_axis_pos), Point(w - bs, x_axis_pos), axis_color);
	line(*output, Point(bs, h - bs), Point(bs, h - bs - gh), axis_color);

	int chw = 6, chh = 10;
	char text[16];

	// y max
	if ((y_max - y_ref) > 0.05 * (y_max - y_min))
	{
		//snprintf(text, sizeof(text)-1, "%.1f", y_max);
		sprintf(text, "%.1f", y_max);
		putText(*output, text, Point(bs / 5, bs - chh / 2), FONT_HERSHEY_PLAIN, 0.55, text_color);
	}
	// y min
	if ((y_ref - y_min) > 0.05 * (y_max - y_min))
	{
		//snprintf(text, sizeof(text)-1, "%.1f", y_min);
		sprintf(text, "%.1f", y_min);
		putText(*output, text, Point(bs / 5, h - bs + chh), FONT_HERSHEY_PLAIN, 0.55, text_color);
	}

	// x axis
	//snprintf(text, sizeof(text)-1, "%.1f", y_ref);
	sprintf(text, "%.1f", y_ref);
	putText(*output, text, Point(bs / 5, x_axis_pos + chh / 2), FONT_HERSHEY_PLAIN, 0.55, text_color);

	// Write the scale of the x axis
	//snprintf(text, sizeof(text)-1, "%.0f", x_max );
	sprintf(text, "%.0f", x_max );
	putText(*output, text, Point(w - bs - strlen(text) * chw, x_axis_pos + chh), FONT_HERSHEY_PLAIN, 0.55, text_color);

	// x min
	//snprintf(text, sizeof(text)-1, "%.0f", x_min );
	sprintf(text, "%.0f", x_min );
	putText(*output, text, Point(bs, x_axis_pos + chh), FONT_HERSHEY_PLAIN, 0.55, text_color);
}

void Figure::drawPlots(Mat *output) {
	int bs = border_size;
	int h = figure_size.height;
	int w = figure_size.width;

	// draw the curves
	for (vector<Series>::iterator it = plots.begin(); it != plots.end(); it++) {
		float *p = it->data;

		// automatically change curve color
		if (it->auto_color == true)
			it->setColor(getAutoColor());

		Point prev_point;
		for (int i=0; i<it->count; i++) {
			int y = round(( p[i] - y_min) * y_scale);
			int x = round((   i  - x_min) * x_scale);
			Point next_point = Point(bs + x, h - (bs + y));
			circle(*output, next_point, 1, it->color, 1);

			// draw a line between two points
			if (i >= 1) {
                if(it->bg_color[i-1] != figure_color) {
                    rectangle(*output, Point(prev_point.x+1, h - bs - 1), Point(next_point.x, bs), it->bg_color[i-1], CV_FILLED);
                }
				line(*output, prev_point, next_point, it->color, 1, CV_AA);
            }
			prev_point = next_point;
		}
	}

}

void Figure::drawLabels(Mat *output, int posx, int posy) {
	// character size
	int chw = 6, chh = 8;

	for (vector<Series>::iterator it = plots.begin(); it != plots.end(); it++) {
		string lbl = it->label;
		// draw label if one is available
		if (lbl.length() > 0) {
			line(*output, Point(posx, posy - chh / 2), Point(posx + 15, posy - chh / 2), it->color, 2, CV_AA);

			putText(*output, lbl.c_str(), Point(posx + 20, posy), FONT_HERSHEY_PLAIN, 0.55, it->color);

			posy += int(chh * 1.5);
		}
	}

}

// whole process of draw a figure.
void Figure::show() {
	initialize();

	Mat *output = new Mat(figure_size, CV_8UC3, figure_color);

	drawAxis(output);

	drawPlots(output);

	drawLabels(output, figure_size.width - 100, 10);

	imshow(figure_name, *output);
	cvWaitKey(1);
	output->release();
}

Mat Figure::getImage() {
	Mat output(figure_size, CV_8UC3, figure_color);
	drawAxis(&output);
	drawPlots(&output);
	drawLabels(&output, figure_size.width - 100, 10);
    return output;
}

// search a named window, return null if not found.
Figure* PlotManager::findFigure(string wnd) {
	for(vector<Figure>::iterator it = figure_list.begin(); it!= figure_list.end(); it++) {
		if (it->getFigureName() == wnd)
			return &(*it);
	}
	return NULL;
}

// plot a new curve, if a figure of the specified figure name already exists,
// the curve will be plot on that figure; if not, a new figure will be created.
void PlotManager::plot(const string figure_name, const float *p, int count, int step, const Scalar line_color, Scalar* background_color) {
	if (count < 1)
		return;

	if (step <= 0)
		step = 1;

	// copy data and create a series format.
	float *data_copy = new float[count];

	for (int i = 0; i < count; i++) {
		*(data_copy + i) = *(p + step * i);
    }

	Series s;
	s.setData(count, data_copy, background_color);

	if (line_color[0] > 0 || line_color[1] > 0 || line_color[2] > 0)
		s.setColor(line_color, false);

	// search the named window and create one if none was found
	active_figure = findFigure(figure_name);
	if (active_figure == NULL) {
		Figure new_figure(figure_name);
		figure_list.push_back(new_figure);
		active_figure = findFigure(figure_name);
		if (active_figure == NULL)
			exit(-1);
	}

	active_series = active_figure->add(s);
	active_figure->show();

}

// add a label to the most recently added curve
void PlotManager::label(string lbl) {
	if((active_series!=NULL) && (active_figure != NULL)) {
		active_series->label = lbl;
		active_figure->show();
	}
}

Mat PlotManager::getImage(const string figure_name) {
	// search the named window and create one if none was found
	active_figure = findFigure(figure_name);
	if (active_figure == NULL) {
        cout << "ERROR" << endl;
        // Error
        exit(-1);
	}

	return active_figure->getImage();
}


// plot a new curve, if a figure of the specified figure name already exists,
// the curve will be plot on that figure; if not, a new figure will be created.
// static method
template<typename T>
void plot(const string figure_name, const T* p, int count, int step, const Scalar line_color, Scalar* background_color) {
	if (step <= 0)
		step = 1;

	float  *data_copy = new float[count * step];

	float   *dst = data_copy;
	const T *src = p;

	for (int i = 0; i < count * step; i++) {
		*dst = (float)(*src);
		dst++;
		src++;
	}

	pm.plot(figure_name, data_copy, count, step, line_color, background_color);

	delete [] data_copy;
}

// delete all plots on a specified figure
void clear(const string figure_name) {
	Figure *fig = pm.findFigure(figure_name);
	if (fig != NULL) {
		fig->clear();
	}
}

// add a label to the most recently added curve
// static method
void label(string lbl) {
	pm.label(lbl);
}

Mat getImage(const string figure_name) {
	return pm.getImage(figure_name);
}

template
void plot(const string figure_name, const unsigned char* p, int count, int step, const Scalar line_color, Scalar* background_color);

template
void plot(const string figure_name, const unsigned long* p, int count, int step, const Scalar line_color, Scalar* background_color);

template
void plot(const string figure_name, const int* p, int count, int step, const Scalar line_color, Scalar* background_color);

template
void plot(const string figure_name, const short* p, int count, int step, const Scalar line_color, Scalar* background_color);
};
