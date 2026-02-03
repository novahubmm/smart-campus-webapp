<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\CurriculumChapter;
use App\Models\CurriculumTopic;

class CurriculumChaptersSeeder extends Seeder
{
    public function run(): void
    {
        // Get the subject from the URL (019b5a7c-3ab3-7352-95cc-6952985233f9)
        $subject = Subject::find('019b5a7c-3ab3-7352-95cc-6952985233f9');
        
        if (!$subject) {
            // If specific subject not found, get any subject or create one
            $subject = Subject::first();
            if (!$subject) {
                $subject = Subject::create([
                    'id' => '019b5a7c-3ab3-7352-95cc-6952985233f9',
                    'name' => 'Mathematics',
                    'code' => 'MATH',
                    'description' => 'Mathematics curriculum with extensive chapters',
                ]);
            }
        }

        $this->command->info("Adding chapters to subject: {$subject->name}");

        // Comprehensive curriculum with many chapters
        $curriculumData = [
            'Chapter 1: Number Systems and Operations' => [
                'Natural Numbers and Counting',
                'Whole Numbers and Zero',
                'Integers and Number Line',
                'Rational Numbers and Fractions',
                'Irrational Numbers',
                'Real Numbers',
                'Complex Numbers Introduction',
                'Number Properties and Operations',
                'Prime Numbers and Factorization',
                'LCM and HCF'
            ],
            'Chapter 2: Algebraic Expressions and Equations' => [
                'Variables and Constants',
                'Algebraic Terms and Coefficients',
                'Like and Unlike Terms',
                'Addition and Subtraction of Algebraic Expressions',
                'Multiplication of Algebraic Expressions',
                'Division of Algebraic Expressions',
                'Linear Equations in One Variable',
                'Linear Equations in Two Variables',
                'Simultaneous Linear Equations',
                'Word Problems in Algebra'
            ],
            'Chapter 3: Polynomials and Factorization' => [
                'Introduction to Polynomials',
                'Types of Polynomials',
                'Addition and Subtraction of Polynomials',
                'Multiplication of Polynomials',
                'Division of Polynomials',
                'Remainder Theorem',
                'Factor Theorem',
                'Factorization by Grouping',
                'Factorization of Quadratic Expressions',
                'Special Products and Factorization'
            ],
            'Chapter 4: Quadratic Equations and Functions' => [
                'Introduction to Quadratic Equations',
                'Standard Form of Quadratic Equations',
                'Solving by Factorization',
                'Completing the Square Method',
                'Quadratic Formula',
                'Nature of Roots',
                'Sum and Product of Roots',
                'Formation of Quadratic Equations',
                'Quadratic Functions and Graphs',
                'Applications of Quadratic Equations'
            ],
            'Chapter 5: Coordinate Geometry' => [
                'Cartesian Coordinate System',
                'Plotting Points on Coordinate Plane',
                'Distance Formula',
                'Section Formula',
                'Area of Triangle using Coordinates',
                'Equation of a Straight Line',
                'Slope of a Line',
                'Different Forms of Line Equations',
                'Parallel and Perpendicular Lines',
                'Applications of Coordinate Geometry'
            ],
            'Chapter 6: Geometry - Lines and Angles' => [
                'Basic Geometric Concepts',
                'Types of Lines',
                'Types of Angles',
                'Angle Pairs and Relationships',
                'Parallel Lines and Transversals',
                'Properties of Parallel Lines',
                'Angle Sum Property',
                'Exterior Angle Theorem',
                'Angle Bisector Properties',
                'Construction of Angles'
            ],
            'Chapter 7: Triangles and Their Properties' => [
                'Classification of Triangles',
                'Angle Sum Property of Triangles',
                'Exterior Angle Property',
                'Congruence of Triangles',
                'Congruence Rules (SSS, SAS, ASA, RHS)',
                'Properties of Isosceles Triangles',
                'Properties of Equilateral Triangles',
                'Inequalities in Triangles',
                'Pythagoras Theorem',
                'Applications of Pythagoras Theorem'
            ],
            'Chapter 8: Quadrilaterals and Polygons' => [
                'Types of Quadrilaterals',
                'Properties of Parallelograms',
                'Properties of Rectangles',
                'Properties of Rhombus',
                'Properties of Squares',
                'Properties of Trapeziums',
                'Angle Sum Property of Quadrilaterals',
                'Regular Polygons',
                'Sum of Interior Angles of Polygons',
                'Sum of Exterior Angles of Polygons'
            ],
            'Chapter 9: Circles and Their Properties' => [
                'Basic Terms Related to Circles',
                'Chords and Their Properties',
                'Equal Chords and Equal Angles',
                'Perpendicular from Center to Chord',
                'Angle in a Semicircle',
                'Angles in the Same Segment',
                'Cyclic Quadrilaterals',
                'Tangent to a Circle',
                'Properties of Tangents',
                'Alternate Segment Theorem'
            ],
            'Chapter 10: Mensuration - Area and Perimeter' => [
                'Perimeter of Basic Shapes',
                'Area of Rectangles and Squares',
                'Area of Parallelograms',
                'Area of Triangles',
                'Area of Trapeziums',
                'Area of Rhombus',
                'Circumference of Circles',
                'Area of Circles',
                'Area of Sectors and Segments',
                'Surface Area of 3D Shapes'
            ],
            'Chapter 11: Volume and Surface Area' => [
                'Volume of Cubes and Cuboids',
                'Surface Area of Cubes and Cuboids',
                'Volume of Cylinders',
                'Surface Area of Cylinders',
                'Volume of Cones',
                'Surface Area of Cones',
                'Volume of Spheres',
                'Surface Area of Spheres',
                'Volume of Composite Solids',
                'Applications in Real Life'
            ],
            'Chapter 12: Trigonometry Basics' => [
                'Introduction to Trigonometry',
                'Trigonometric Ratios',
                'Sine, Cosine, and Tangent',
                'Trigonometric Ratios of Special Angles',
                'Trigonometric Identities',
                'Complementary Angles',
                'Heights and Distances',
                'Angle of Elevation',
                'Angle of Depression',
                'Applications of Trigonometry'
            ],
            'Chapter 13: Statistics and Data Handling' => [
                'Collection of Data',
                'Organization of Data',
                'Frequency Distribution',
                'Grouped and Ungrouped Data',
                'Measures of Central Tendency',
                'Mean, Median, and Mode',
                'Range and Quartiles',
                'Graphical Representation of Data',
                'Bar Graphs and Histograms',
                'Pie Charts and Line Graphs'
            ],
            'Chapter 14: Probability' => [
                'Introduction to Probability',
                'Experimental Probability',
                'Theoretical Probability',
                'Equally Likely Outcomes',
                'Probability of an Event',
                'Probability of Complementary Events',
                'Addition Rule of Probability',
                'Multiplication Rule of Probability',
                'Conditional Probability',
                'Applications of Probability'
            ],
            'Chapter 15: Sequences and Series' => [
                'Introduction to Sequences',
                'Arithmetic Progressions',
                'nth Term of AP',
                'Sum of n Terms of AP',
                'Geometric Progressions',
                'nth Term of GP',
                'Sum of n Terms of GP',
                'Sum to Infinity of GP',
                'Arithmetic Mean',
                'Geometric Mean'
            ]
        ];

        $chapterOrder = 1;
        foreach ($curriculumData as $chapterTitle => $topics) {
            $this->command->info("Creating chapter: {$chapterTitle}");
            
            $chapter = CurriculumChapter::create([
                'id' => \Illuminate\Support\Str::uuid(),
                'subject_id' => $subject->id,
                'title' => $chapterTitle,
                'description' => 'Comprehensive study of ' . $chapterTitle,
                'order' => $chapterOrder++,
            ]);

            $topicOrder = 1;
            foreach ($topics as $topicTitle) {
                CurriculumTopic::create([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'chapter_id' => $chapter->id,
                    'title' => $topicTitle,
                    'description' => 'Detailed understanding of ' . $topicTitle,
                    'order' => $topicOrder++,
                ]);
            }
        }

        $this->command->info("Successfully created {$chapterOrder} chapters with multiple topics each!");
    }
}